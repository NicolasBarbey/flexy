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

namespace FlexyBundle\UiComponents\Checkout\AddressCardCheckout;

use FlexyBundle\Service\FlexyCheckoutService;
use FlexyBundle\UiComponents\AddressCard\AddressCard;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent(name: "Flexy:Checkout:AddressCardCheckout", template: '@UiComponents/Checkout/AddressCardCheckout/AddressCardCheckout.html.twig')]
class AddressCardCheckout extends AddressCard
{
    public bool $hasForm = true;
    public bool $checked = false;

    public function __construct(private readonly FlexyCheckoutService $flexyCheckoutService) {}


    #[PostMount()]
    public function postMount()
    {
        $savedAddress = $this->flexyCheckoutService->getDeliveryAddress();
        if (null !== $savedAddress) {
            $this->checked = $this->flexyCheckoutService->getDeliveryAddress() === $this->addressId;
        }
    }
}
