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

namespace FlexyBundle\UiComponents\Checkout\Delivery\StoreDelivery;

use FlexyBundle\UiComponents\Checkout\Delivery\DeliveryMode\DeliveryModeTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Domain\Cart\CartFacade;
use TwigEngine\Service\DataAccess\AttributeAccessService;

#[AsTwigComponent(name: "Flexy:Checkout:Delivery:StoreDelivery", template: '@UiComponents/Checkout/Delivery/StoreDelivery/StoreDelivery.html.twig')]
class StoreDelivery
{
    use DeliveryModeTrait;

    public string $type = "StoreDelivery";
    public bool $closed = false;

    public function __construct(
        private readonly AttributeAccessService $attributeAccessService,
        private readonly CartFacade             $cartFacade,
    ) {}

    public function getAddress(): array
    {
        return [
            'address1' => $this->attributeAccessService->attributeConfig('store_address1'),
            'address2' => $this->attributeAccessService->attributeConfig('store_address2'),
            'address3' => $this->attributeAccessService->attributeConfig('store_address3'),
            'zipCode' => $this->attributeAccessService->attributeConfig('store_zipcode'),
            'city' => $this->attributeAccessService->attributeConfig('store_city'),
        ];
    }
    public function getHours(): array
    {
        return [
            [
                'day' => 'Lundi (fake)',
                'hours' => '14h - 19h'
            ],
            [
                'day' => 'Mardi (fake)',
                'hours' => '14h - 19h'
            ]
        ];
    }

    public function getChecked(): bool
    {
        return $this->cartFacade->getDeliveryModuleId() === $this->moduleId;
    }
}
