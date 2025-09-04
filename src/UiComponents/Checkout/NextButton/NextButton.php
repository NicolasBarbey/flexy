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

namespace FlexyBundle\UiComponents\Checkout\NextButton;

use FlexyBundle\UiComponents\Checkout\CheckoutEvents;
use FlexyBundle\UiComponents\Checkout\CheckoutSteps;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Domain\Cart\CartFacade;
use Thelia\Domain\Shipping\ShippingFacade;

#[AsLiveComponent(name: 'Flexy:Checkout:NextButton', template: '@UiComponents/Checkout/NextButton/NextButton.html.twig')]
class NextButton
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp(updateFromParent: true)]
    public int $step;

    #[LiveProp(updateFromParent: true)]
    public string $href;

    public function __construct(
        private readonly CartFacade $cartFacade,
        private readonly ShippingFacade $shippingFacade,
    ) {
    }

    public function mount(int $step, string $href): void
    {
        $this->step = $step;
        $this->href = $href;
    }

    #[LiveListener(CheckoutEvents::SET_DELIVERY_MODULE_OPTION)]
    #[LiveListener(CheckoutEvents::SET_DELIVERY_ORDER_ADDRESS_ID)]
    #[LiveListener(CheckoutEvents::SET_INVOICE_ORDER_ADDRESS_ID)]
    #[LiveListener(CheckoutEvents::DELETE_ITEM_EVENT)]
    #[LiveListener(CheckoutEvents::ADD_ITEM_EVENT)]
    public function getIsValid(): bool
    {
        if ($this->step === CheckoutSteps::CART) {
            return $this->isCartValid();
        }
        if ($this->step === CheckoutSteps::DELIVERY) {
            return $this->isDeliveryValid();
        }

        return false;
    }

    private function isCartValid(): bool
    {
        return $this->cartFacade->getOrCreateFromSession()->countCartItems() > 0;
    }

    private function isDeliveryValid(): bool
    {
        $cart = $this->cartFacade->getOrCreateFromSession();
        // test home delivery
        if (
            $this->isCartValid()
            && $cart->getAddressDeliveryId()
            && $cart->getDeliveryModuleId()
        ) {
            return true;
        }

        // @TODO test local pickup & pickup
        return false;
    }
}
