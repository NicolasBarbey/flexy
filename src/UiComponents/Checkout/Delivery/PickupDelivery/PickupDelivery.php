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

namespace FlexyBundle\UiComponents\Checkout\Delivery\PickupDelivery;

use FlexyBundle\UiComponents\Checkout\Delivery\DeliveryMode\DeliveryModeTrait;
use Psr\Log\LoggerInterface;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Domain\Addressing\Service\AddressService;
use Thelia\Domain\Cart\CartFacade;

#[AsTwigComponent(name: 'Flexy:Checkout:Delivery:PickupDelivery', template: '@UiComponents/Checkout/Delivery/PickupDelivery/PickupDelivery.html.twig')]
class PickupDelivery
{
    use DeliveryModeTrait;

    public string $type = 'PickupDelivery';

    public function __construct(
        private readonly Session $session,
        private readonly AddressService $addressService,
        private readonly CartFacade $cartFacade,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function mount(int $moduleId, string $optionCode): void
    {
        $this->moduleId = $moduleId;
        $this->optionCode = $optionCode;
    }

    public function getChecked(): bool
    {
        return $this->cartFacade->getDeliveryModuleId() === $this->moduleId && $this->session->get('deliveryModuleOption') === $this->optionCode;
    }
}
