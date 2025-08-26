<?php

namespace FlexyBundle\UiComponents\Checkout\Summary;


use FlexyBundle\UiComponents\Checkout\CheckoutEvents;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use TwigEngine\Service\DataAccess\AttributeAccessService;

#[AsLiveComponent(name: "Flexy:Checkout:Summary", template: '@UiComponents/Checkout/Summary/Summary.html.twig')]
class Summary
{

    use ComponentToolsTrait;
    use DefaultActionTrait;

    public function __construct(private readonly AttributeAccessService $attributeAccessService) {}


    #[LiveListener(CheckoutEvents::DELETE_ITEM_EVENT)]
    #[LiveListener(CheckoutEvents::UPDATE_ITEM_QUANTITY_EVENT)]
    #[LiveListener(CheckoutEvents::ADD_ITEM_EVENT)]
    #[LiveListener(CheckoutEvents::SET_DELIVERY_MODULE_OPTION)]
    #[LiveListener(CheckoutEvents::SET_DELIVERY_ORDER_ADDRESS_ID)]
    public function syncCart(): void {}

    public function getSummary()
    {
        return [
            'item_count' =>  $this->attributeAccessService->attributeCart('item_count'),
            'raw_taxed_total_price' => $this->attributeAccessService->attributeCart('raw_taxed_total_price'),
            'total_taxed_price' => $this->attributeAccessService->attributeCart('total_taxed_price'),
            'total_tax_amount' => $this->attributeAccessService->attributeCart('total_tax_amount'),
            'taxed_postage' => $this->attributeAccessService->attributeCart('taxed_postage'),
        ];
    }
}
