<?php

namespace FlexyBundle\UiComponents\Checkout\NextButton;


use FlexyBundle\UiComponents\Checkout\CheckoutEvents;
use FlexyBundle\UiComponents\Checkout\CheckoutSteps;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Domain\Cart\CartService;
use Thelia\Domain\Shipping\Service\DeliveryService;

#[AsLiveComponent(name: "Flexy:Checkout:NextButton", template: '@UiComponents/Checkout/NextButton/NextButton.html.twig')]
class NextButton
{

    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp(updateFromParent: true)]
    public int $step;

    #[LiveProp(updateFromParent: true)]
    public string $href;

    public function __construct(
        private readonly CartService $cartService,
        private readonly DeliveryService $deliveryService,
    ) {}

    public function mount(int $step, string $href)
    {
        $this->step = $step;
        $this->href = $href;
    }

    #[LiveListener(CheckoutEvents::SET_DELIVERY_MODULE_OPTION)]
    #[LiveListener(CheckoutEvents::SET_DELIVERY_ORDER_ADDRESS_ID)]
    #[LiveListener(CheckoutEvents::DELETE_ITEM_EVENT)]
    #[LiveListener(CheckoutEvents::ADD_ITEM_EVENT)]
    public function getIsValid()
    {

        if ($this->step === CheckoutSteps::CART) {
            return $this->isCartValid();
        }
        if ($this->step === CheckoutSteps::DELIVERY) {
            return $this->isDeliveryValid();
        }
        return false;
    }

    private function isCartValid()
    {
        if ($this->cartService->getCart()->countCartItems() > 0) {
            return true;
        }

        return false;
    }

    private function isDeliveryValid()
    {
        // test home delivery
        if (
            $this->isCartValid()
            && $this->cartService->getCart()->getAddressDeliveryId()
            && $this->cartService->getCart()->getDeliveryModuleId()
        ) {
            return true;
        }

        // TODO test local picup & picku^p
        return false;
    }
}
