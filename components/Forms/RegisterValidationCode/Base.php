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

namespace FlexyBundle\Components\Forms\RegisterValidationCode;

use FlexyBundle\Form\CustomerActivationForm;
use Propel\Runtime\Exception\PropelException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Domain\Customer\Service\CustomerCodeManager;
use Thelia\Model\Customer;
use Thelia\Model\CustomerQuery;

#[AsLiveComponent]
class Base extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public bool $activated = false;

    public function __construct(
        private readonly CustomerCodeManager $customerCodeProcessor,
        private readonly LoggerInterface $logger,
        private readonly RequestStack $requestStack,
        #[Autowire(service: 'translator')]
        private readonly TranslatorInterface $translator,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(CustomerActivationForm::class);
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();

        // The account being activated is the pending registration of this session. Form
        // values reach the server unsigned, so an address read from the form would let a
        // caller check codes against any account but their own.
        $customer = $this->retrievePendingCustomer();

        if (!$customer instanceof Customer) {
            $this->addFlash('error', $this->translator->trans('Your registration could not be found. Please register again.'));

            return;
        }

        try {
            $this->customerCodeProcessor->activateCustomerByCode(
                (string) $customer->getEmail(),
                (string) $this->getForm()->get('activation_code')->getData()
            );
            $this->activated = true;
        } catch (PropelException $e) {
            $this->logger->error('Customer activation failed: '.$e->getMessage());
            $this->addFlash('error', $this->translator->trans('An unexpected error occurred. Please try again later.'));
        } catch (\Exception) {
            // One message for an expired code, a wrong code and a missing one: the
            // difference is of no use to the person who received the mail, and of use
            // to someone guessing.
            $this->addFlash('error', $this->translator->trans('This activation code is not valid, or is no longer valid. Please ask for a new one.'));
        }
    }

    private function retrievePendingCustomer(): ?Customer
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request || !$request->hasSession()) {
            return null;
        }

        $customerId = $request->getSession()->get('registration_customer_id');

        return $customerId ? CustomerQuery::create()->findPk($customerId) : null;
    }
}
