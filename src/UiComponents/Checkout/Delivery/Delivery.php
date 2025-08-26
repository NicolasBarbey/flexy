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
use Thelia\Service\Model\CartService;
use Thelia\Service\Model\DeliveryService;

#[AsLiveComponent(name: "Flexy:Checkout:Delivery", template: '@UiComponents/Checkout/Delivery/Delivery.html.twig')]
class Delivery
{

    use ComponentToolsTrait;
    use DefaultActionTrait;


    #[LiveProp]
    public ?int $deliveryModuleId = null;

    #[LiveProp]
    public ?int $deliveryAddressId = null;

    public function __construct(private readonly DeliveryService $deliveryModuleService, private readonly CartService $cartService, private readonly FlexyCheckoutService $flexyCheckoutService) {}

    public function mount()
    {
        $this->deliveryAddressId = $this->flexyCheckoutService->getDeliveryAddress();
    }

    public function getDeliveryModulesOptions()
    {
        $deliveryModules = $this->deliveryModuleService->getValidDeliveryModuleCollection()->getAll();

        $deliveryOptions = [];

        foreach ($deliveryModules as $module) {
            if (!isset($module['valid']) || !$module['valid']) {
                continue;
            }

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

    public function getCart()
    {
        $cart = $this->cartService->getCart();
        $items = $cart->getCartItems();
        return [
            ...$cart->toArray(TableMap::TYPE_CAMELNAME),
            'items' => $items->toArray(null, false, TableMap::TYPE_CAMELNAME),
            'totalItems' => $cart->countCartItems(),
        ];
    }

    #[LiveListener(CheckoutEvents::SET_DELIVERY_MODULE_OPTION)]
    public function selectDeliveryModuleOption(#[LiveArg] string $optionCode, #[LiveArg] int $moduleId)
    {
        $this->flexyCheckoutService->setDeliveryAddress(null);
        $this->deliveryAddressId = null;

        $this->flexyCheckoutService->setDeliveryModule($moduleId);
    }

    #[LiveListener(CheckoutEvents::SET_DELIVERY_ORDER_ADDRESS_ID)]
    public function selectDeliveryAddress(#[LiveArg] int $addressId)
    {
        $this->flexyCheckoutService->setDeliveryAddress($addressId);
        $this->deliveryAddressId = $addressId;
    }
}
