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

namespace FlexyBundle\UiComponents\AccountAddressForm;

use FlexyBundle\Form\AddressEditForm;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Api\Service\DataAccess\DataAccessService;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Domain\Addressing\Service\AddressService;
use Thelia\Domain\Customer\CustomerFacade;
use Thelia\Model\AddressQuery;
use Thelia\Model\Customer;
use TwigEngine\Service\FormService;

#[AsLiveComponent(name: 'Flexy:AccountAddressForm', template: '@UiComponents/AccountAddressForm/AccountAddressForm.html.twig')]
class AccountAddressForm extends BaseFrontController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    public ?int $addressId = null;
    public ?string $action = '';
    public ?string $successParam = '';

    public ?Customer $customer;

    public function __construct(
        private readonly FormService $formService,
        private readonly AddressService $addressService,
        private readonly FormFactoryInterface $formFactory,
        private readonly DataAccessService $dataAccessService,
        private readonly CustomerFacade $customerFacade,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        $form = $this->formService->getFormByName(AddressEditForm::FORM_NAME, $this->getAddress());

        if ($this->getCustomerHasOneAddress() && $this->addressId) {
            $form->remove('is_default');
            $form->add('is_default', HiddenType::class, [
                'data' => 1,
            ]);
        }

        $form->add('error_url', HiddenType::class, [
            'data' => $this->requestStack->getCurrentRequest()->getUri(),
        ]);

        return $form;
    }

    public function getAddress(): array
    {
        if (!$this->addressId) {
            return [];
        }
        $address = AddressQuery::create()->findPk($this->addressId);

        return $this->addressService->mapModelToFormData($address);
    }

    public function getCustomerHasOneAddress(): bool
    {
        $addresses = $this->dataAccessService->resources('/api/front/account/addresses', [
            'customer.id' => $this->customerFacade->getCurrentCustomer()->getId(),
        ]);
        return \count($addresses) <= 1;
    }
}
