<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FlexyBundle\UiComponents\Checkout\Delivery\HomeDelivery;

use FlexyBundle\Service\FlexyCheckoutService;
use FlexyBundle\UiComponents\Checkout\CheckoutEvents;
use FlexyBundle\UiComponents\Checkout\Delivery\DeliveryMode\DeliveryMode;
use FlexyBundle\UiComponents\Checkout\Delivery\DeliveryMode\DeliveryModeTrait;
use Propel\Runtime\Map\TableMap;
use Psr\Log\LoggerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Log\Tlog;
use Thelia\Service\Model\AddressService;
use Thelia\Service\Model\CartService;
use TwigEngine\Service\DataAccess\AttributeAccessService;

#[AsLiveComponent(name: "Flexy:Checkout:Delivery:HomeDelivery", template: '@UiComponents/Checkout/Delivery/HomeDelivery/HomeDelivery.html.twig')]
class HomeDelivery
{
    use DefaultActionTrait;
    use ComponentToolsTrait;
    use DeliveryModeTrait;

    public string $type = "HomeDelivery";

    #[LiveProp(updateFromParent: true)]
    public int $moduleId;

    #[LiveProp(updateFromParent: true)]
    public string $optionCode;

    #[LiveProp(updateFromParent: true)]
    public string $title;

    #[LiveProp(updateFromParent: true)]
    public string $date;

    #[LiveProp(updateFromParent: true)]
    public string $price;

    #[LiveProp(updateFromParent: true)]
    public ?string $icon = null;

    #[LiveProp]
    public bool $showNewAddressForm = false;

    #[LiveProp]
    public bool $showEditAddressForm = false;

    #[LiveProp]
    public ?int $editingAddressId = null;

    public function __construct(
        private readonly Session $session,
        private readonly AddressService $addressService,
        private readonly CartService $cartService,
        private readonly FlexyCheckoutService $flexyCheckoutService,
        private readonly LoggerInterface $logger
    ) {}

    public function mount(string $icon, string $moduleId): void
    {

        $this->icon = $icon;
        $this->moduleId = $moduleId;
    }

    #[LiveListener(CheckoutEvents::SET_DELIVERY_MODULE_OPTION)]
    #[LiveListener(CheckoutEvents::ADD_NEW_DELIVERY_ADDRESS)]
    #[LiveListener('cancelAddressForm')]
    public function resetAddressForm()
    {
        $this->showNewAddressForm = false;
        $this->editingAddressId = null;
    }


    #[LiveListener(CheckoutEvents::EDIT_DELIVERY_ADDRESS)]
    public function setEditingAddress(#[LiveArg] int $addressId)
    {
        $this->editingAddressId = $addressId;
    }

    #[LiveListener(CheckoutEvents::DELETE_DELIVERY_ADDRESS)]
    public function deleteAddress(#[LiveArg] int $addressId)
    {

        try {
            $this->addressService->deleteAddress($addressId);
        } catch (\Exception $e) {
            $this->logger->error(\sprintf('Error during address deletion : %s', $e->getMessage()));
        }
    }

    #[LiveAction]
    public function toggleNewAddressForm()
    {
        $this->showNewAddressForm = !$this->showNewAddressForm;
    }

    public function getChecked()
    {
        return $this->flexyCheckoutService->getDeliveryModule() === $this->moduleId;
    }

    public function getAddressList()
    {
        /** @var Customer $user */
        $user = $this->session->getCustomerUser();
        return $user->getAddresses()?->toArray(null, false, TableMap::TYPE_CAMELNAME);
    }
}
