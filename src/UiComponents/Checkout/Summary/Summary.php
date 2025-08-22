<?php

namespace FlexyBundle\UiComponents\Checkout\Summary;

use FlexyBundle\UiComponents\Checkout\Cart\Cart;
use FlexyBundle\UiComponents\Checkout\CartManipulationTrait;
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

    #[LiveListener(Cart::DELETE_ITEM_EVENT)]
    #[LiveListener(Cart::UPDATE_ITEM_QUANTITY_EVENT)]
    #[LiveListener(Cart::ADD_ITEM_EVENT)]
    public function syncCart(): void {}
}
