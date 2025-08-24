<?php

namespace FlexyBundle\UiComponents\Checkout\Summary;

use FlexyBundle\UiComponents\Checkout\Cart\Cart;
use FlexyBundle\UiComponents\Checkout\CartManipulationTrait;
use FlexyBundle\UiComponents\Checkout\CheckoutEvents;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: "Flexy:Checkout:Summary", template: '@UiComponents/Checkout/Summary/Summary.html.twig')]
class Summary
{

    use ComponentToolsTrait;
    use DefaultActionTrait;
    use CartManipulationTrait;

    public function __construct() {}

    #[LiveListener(CheckoutEvents::DELETE_ITEM_EVENT)]
    #[LiveListener(CheckoutEvents::UPDATE_ITEM_QUANTITY_EVENT)]
    #[LiveListener(CheckoutEvents::ADD_ITEM_EVENT)]
    #[LiveListener(CheckoutEvents::SET_DELIVERY_MODULE_OPTION)]
    public function syncCart(): void {}
}
