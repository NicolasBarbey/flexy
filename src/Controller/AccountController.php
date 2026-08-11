<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FlexyBundle\Controller;

use FlexyBundle\Form\AddressEditForm;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Api\Service\DataAccess\DataAccessService;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Domain\Addressing\Exception\AddressNotFoundException;
use Thelia\Domain\Addressing\Service\AddressService;
use Thelia\Domain\Customer\DTO\CustomerRegisterDTO;
use Thelia\Domain\Customer\Exception\CustomerException;
use Thelia\Domain\Customer\Service\CustomerUpdateService;
use Thelia\Form\Definition\FrontForm;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\AddressQuery;
use Thelia\Model\Event\AddressEvent;

#[Route('/account', name: 'account_')]
class AccountController extends FlexyController
{
    public const ADDRESS_ACTION_TOKEN_ID = 'address_action';

    #[Route('', name: 'index')]
    public function index(): Response
    {
        $this->checkAuth();

        return $this->render('account');
    }

    #[Route('/addresses', name: 'addresses')]
    public function addresses(): Response
    {
        $this->checkAuth();

        return $this->render('account-addresses');
    }

    #[Route('/address/new', name: 'address_new', methods: ['GET'])]
    public function addressNew(): Response
    {
        $this->checkAuth();

        return $this->render('address');
    }

    #[Route('/address/new', name: 'address_create', methods: ['POST'])]
    public function addressCreate(AddressService $addressService): Response
    {
        $this->checkAuth();

        return $this->saveAddress($addressService, null);
    }

    #[Route('/address/{addressId}', name: 'address', requirements: ['addressId' => '\d+'], methods: ['GET'])]
    public function address(DataAccessService $dataAccessService, int $addressId): Response
    {
        $this->checkOwnedAddress($addressId);

        // Same reason as the order page: an empty API result must not render in 200.
        if (null === $dataAccessService->resources('/api/front/account/addresses/'.$addressId)) {
            throw new NotFoundHttpException();
        }

        return $this->render('address-update', ['addressId' => $addressId]);
    }

    #[Route('/address/{addressId}', name: 'address_update', requirements: ['addressId' => '\d+'], methods: ['POST'])]
    public function addressUpdate(AddressService $addressService, int $addressId): Response
    {
        $this->checkAuth();

        return $this->saveAddress($addressService, $addressId);
    }

    #[Route('/address/delete/{addressId}', name: 'address_delete', requirements: ['addressId' => '\d+'], methods: ['POST'])]
    public function addressDelete(
        AddressService $addressService,
        CsrfTokenManagerInterface $csrfTokenManager,
        Request $request,
        int $addressId,
    ): RedirectResponse {
        $this->checkAuth();
        $this->checkAddressActionToken($csrfTokenManager, $request);

        try {
            $addressService->deleteAddress($addressId);
        } catch (AddressNotFoundException|CustomerException $e) {
            $this->logger->error(\sprintf('Error during address deletion process: %s.', $e->getMessage()));

            return $this->generateRedirect($this->generateUrl('account_addresses', ['error' => true]));
        }

        return $this->generateRedirect($this->generateUrl('account_addresses', ['delete_success' => true]));
    }

    #[Route('/address/default/{addressId}', name: 'address_default', requirements: ['addressId' => '\d+'], methods: ['POST'])]
    public function addressDefault(
        EventDispatcherInterface $eventDispatcher,
        CsrfTokenManagerInterface $csrfTokenManager,
        Request $request,
        int $addressId,
    ): RedirectResponse {
        $this->checkAuth();
        $this->checkAddressActionToken($csrfTokenManager, $request);

        $address = AddressQuery::create()
            ->filterByCustomerId($this->getSecurityContext()->getCustomerUser()?->getId())
            ->findPk($addressId)
        ;

        if (null === $address) {
            return $this->generateRedirect($this->generateUrl('account_addresses', ['error' => true]));
        }

        $eventDispatcher->dispatch(new AddressEvent($address), TheliaEvents::ADDRESS_DEFAULT);

        return $this->generateRedirect($this->generateUrl('account_addresses', ['default_success' => true]));
    }

    #[Route('/password', name: 'password', methods: ['POST'])]
    public function password(CustomerUpdateService $customerUpdateService): Response
    {
        $this->checkAuth();

        $form = $this->createForm(FrontForm::CUSTOMER_PASSWORD_UPDATE);

        try {
            $validatedForm = $this->validateForm($form, Request::METHOD_POST);

            // updateProfile() guards every field with a null check, so a password-only
            // DTO leaves the rest of the profile untouched.
            $customerUpdateService->updateCustomer(
                new CustomerRegisterDTO(password: $validatedForm->get('password')->getData()),
                $this->getSecurityContext()->getCustomerUser(),
            );

            // A stolen session id must not survive the very action taken to lock an
            // attacker out. Attributes are carried over, so the customer stays signed in.
            $this->getSession()->migrate(true);

            return $this->generateSuccessRedirect($form);
        } catch (FormValidationException $e) {
            $message = $this->translator->trans('Please check your input: %s', ['%s' => $e->getMessage()]);
        }

        $this->logger->error(\sprintf('Error during customer password modification process: %s.', $message));

        $form->setErrorMessage($message);
        $this->parserContext
            ->addForm($form)
            ->setGeneralError($message)
        ;

        if ($form->hasErrorUrl()) {
            return $this->generateErrorRedirect($form);
        }

        return $this->generateRedirectFromRoute('account_index');
    }

    /**
     * Both address actions change state, so they are POST-only and carry a CSRF token:
     * the session cookie is SameSite=Lax, which a top-level navigation would still send.
     */
    private function checkAddressActionToken(CsrfTokenManagerInterface $csrfTokenManager, Request $request): void
    {
        $token = new CsrfToken(self::ADDRESS_ACTION_TOKEN_ID, (string) $request->request->get('_token'));

        if (!$csrfTokenManager->isTokenValid($token)) {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * The core leaves `Get /front/account/addresses/{id}` without a security expression,
     * so nothing downstream checks who owns the address: the check belongs here.
     * Answers like a missing address so an id cannot be probed for existence.
     */
    private function checkOwnedAddress(int $addressId): void
    {
        $this->checkAuth();

        $isOwner = AddressQuery::create()
            ->filterByCustomerId($this->getSecurityContext()->getCustomerUser()?->getId())
            ->filterById($addressId)
            ->exists()
        ;

        if (!$isOwner) {
            throw new NotFoundHttpException();
        }
    }

    private function saveAddress(AddressService $addressService, ?int $addressId): Response
    {
        $form = $this->createForm(AddressEditForm::FORM_NAME);

        try {
            $validatedForm = $this->validateForm($form, Request::METHOD_POST);
            $addressService->updateOrCreateAddress($addressId, $validatedForm);

            return $this->generateSuccessRedirect($form);
        } catch (FormValidationException $e) {
            $message = $this->translator->trans('Please check your input: %s', ['%s' => $e->getMessage()]);
        } catch (AddressNotFoundException|CustomerException $e) {
            $message = $this->translator->trans('An error has occurred, please try again later');
        }

        $this->logger->error(\sprintf('Error during address save process: %s.', $message));

        $form->setErrorMessage($message);
        $this->parserContext
            ->addForm($form)
            ->setGeneralError($message)
        ;

        if ($form->hasErrorUrl()) {
            return $this->generateErrorRedirect($form);
        }

        return $this->generateRedirectFromRoute('account_addresses');
    }
}
