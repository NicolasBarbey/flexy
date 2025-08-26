<?php

namespace FlexyBundle\UiComponents\Checkout\Payment;

use FlexyBundle\Service\FlexyCheckoutService;
use FlexyBundle\UiComponents\Checkout\CheckoutEvents;
use Propel\Runtime\Map\TableMap;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Api\Service\DataAccess\DataAccessService;
use Thelia\Service\Model\CartService;
use Thelia\Service\Model\DeliveryService;

#[AsLiveComponent(name: "Flexy:Checkout:Payment", template: '@UiComponents/Checkout/Payment/Payment.html.twig')]
class Payment
{

    use ComponentToolsTrait;
    use DefaultActionTrait;


    #[LiveProp]
    public ?int $paymentModuleId = null;

    public function __construct(private readonly DataAccessService $dataAccessService) {}

    public function mount() {}

    public function getModules()
    {
        $modules = $this->dataAccessService->resources('/api/front/payment/modules');

        return $modules;
    }
}
