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

namespace FlexyBundle\UiComponents\Checkout\DeliveryModes\HomeDelivery;

use FlexyBundle\UiComponents\Checkout\DeliveryModes\DeliveryMode\DeliveryMode;
use Propel\Runtime\Map\TableMap;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Service\Model\AddressService;
use Thelia\Service\Model\CartService;
use TwigEngine\Service\DataAccess\AttributeAccessService;

#[AsTwigComponent(name: "Flexy:Checkout:DeliveryModes:HomeDelivery", template: '@UiComponents/Checkout/DeliveryModes/HomeDelivery/HomeDelivery.html.twig')]
class HomeDelivery extends DeliveryMode
{

    public string $type = "HomeDelivery";
    public array $addresses = [];

    public function __construct(
        private readonly Session $session,
        private readonly AddressService $addressService,
        private readonly CartService $cartService,
    ) {}

    public function mount(): void
    {
        /** @var Customer $user */
        $user = $this->session->getCustomerUser();
        $this->addresses = $user->getAddresses()?->toArray(null, false, TableMap::TYPE_CAMELNAME);
        //$this->deliveryAddressId = $this->cartService->getCart()->getAddressDeliveryId();
        //$this->invoiceAddressId = $this->cartService->getCart()->getAddressInvoiceId();
    }
}
