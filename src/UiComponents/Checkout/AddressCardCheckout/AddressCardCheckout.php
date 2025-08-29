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

namespace FlexyBundle\UiComponents\Checkout\AddressCardCheckout;

use FlexyBundle\UiComponents\AddressCard\AddressCard;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Thelia\Domain\Cart\CartFacade;

#[AsTwigComponent(name: 'Flexy:Checkout:AddressCardCheckout', template: '@UiComponents/Checkout/AddressCardCheckout/AddressCardCheckout.html.twig')]
class AddressCardCheckout extends AddressCard
{
    public bool $hasForm = true;
    public bool $checked = false;

    public function __construct(private readonly CartFacade $cartFacade)
    {
    }

    #[PostMount]
    public function postMount(): void
    {
        $savedAddress = $this->cartFacade->getDeliveryAddressId();
        if (null !== $savedAddress) {
            $this->checked = $this->cartFacade->getDeliveryAddressId() === $this->addressId;
        }
    }
}
