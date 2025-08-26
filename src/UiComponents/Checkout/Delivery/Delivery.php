<?php

namespace FlexyBundle\UiComponents\Checkout\Delivery;

use FlexyBundle\Service\FlexyCheckoutService;
use FlexyBundle\UiComponents\Checkout\CheckoutEvents;
use Propel\Runtime\Map\TableMap;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Domain\Cart\CartFacade;
use Thelia\Domain\Cart\CartService;
use Thelia\Domain\Cart\Service\CartRetriever;
use Thelia\Domain\Checkout\CheckoutFacade;
use Thelia\Domain\Checkout\DTO\CheckoutDTO;
use Thelia\Domain\Customer\CustomerFacade;
use Thelia\Domain\Shipping\Service\DeliveryService;
use Thelia\Domain\Shipping\ShippingFacade;
use Thelia\Model\Module;

#[AsLiveComponent(name: "Flexy:Checkout:Delivery", template: '@UiComponents/Checkout/Delivery/Delivery.html.twig')]
class Delivery
{

    use ComponentToolsTrait;
    use DefaultActionTrait;


    #[LiveProp]
    public ?int $deliveryModuleId = null;

    #[LiveProp]
    public ?int $deliveryAddressId = null;

    public function __construct(
        private readonly ShippingFacade $shippingFacade,
        private readonly CartFacade $cartFacade,
        private readonly CheckoutFacade $checkoutFacade,
        private readonly CustomerFacade $customerFacade,
    ) {
    }

    public function mount(): void
    {
        $this->deliveryAddressId = $this->cartFacade->getDeliveryAddressId();

    }

    public function getDeliveryModulesOptions(): array
    {
        $cart = $this->cartFacade->getOrCreateForCustomer($this->customerFacade->getCurrentCustomer());
        $deliveryModules = $this->shippingFacade->listValidMethods($cart);

        $deliveryOptions = [];

        /** @var Module $module */
        foreach ($deliveryModules as $module) {
            // @todo
            foreach ($module['options'] as $option) {
                $deliveryOptions[$option['code']] = [
                    'title' => isset($module['title']) ? $module['title'] : $module['code'],
                    'moduleId' => $module['id'],
                    'deliveryMode' => $module['deliveryMode'],
                    ...$option
                ];
            }
        }

        return $deliveryOptions;
    }

    public function getCart(): array
    {
        $cart = $this->cartFacade->getOrCreateForCustomer($this->customerFacade->getCurrentCustomer());
        $items = $cart->getCartItems();
        return [
            ...$cart->toArray(TableMap::TYPE_CAMELNAME),
            'items' => $items->toArray(null, false, TableMap::TYPE_CAMELNAME),
            'totalItems' => $cart->countCartItems(),
        ];
    }

    #[LiveListener(CheckoutEvents::SET_DELIVERY_MODULE_OPTION)]
    public function selectDeliveryModuleOption(#[LiveArg] string $optionCode, #[LiveArg] int $moduleId): void
    {
        $this->cartFacade->setDeliveryAddress(new CheckoutDTO(
            $this->cartFacade->getOrCreateForCustomer($this->customerFacade->getCurrentCustomer())
        ));
        $this->deliveryAddressId = null;

        $this->cartFacade->setDeliveryModule(new CheckoutDTO(
            cart: $this->cartFacade->getOrCreateForCustomer($this->customerFacade->getCurrentCustomer()),
            deliveryModuleId: $moduleId,
        ));
    }

    #[LiveListener(CheckoutEvents::SET_DELIVERY_ORDER_ADDRESS_ID)]
    public function selectDeliveryAddress(#[LiveArg] int $addressId): void
    {
        $this->cartFacade->setDeliveryAddress(new CheckoutDTO(
            cart: $this->cartFacade->getOrCreateForCustomer($this->customerFacade->getCurrentCustomer()),
            deliveryAddressId: $addressId,
        ));
        $this->deliveryAddressId = $addressId;
    }
}
