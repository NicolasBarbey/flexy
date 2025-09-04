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

namespace FlexyBundle\UiComponents\Checkout\Delivery;

use FlexyBundle\UiComponents\Checkout\CheckoutEvents;
use Propel\Runtime\Map\TableMap;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Thelia\Api\Resource\DeliveryModuleOption;
use Thelia\Domain\Cart\CartFacade;
use Thelia\Domain\Checkout\CheckoutFacade;
use Thelia\Domain\Checkout\DTO\CheckoutDTO;
use Thelia\Domain\Shipping\Service\DeliveryPostageQuerier;
use Thelia\Domain\Shipping\ShippingFacade;

#[AsLiveComponent(name: 'Flexy:Checkout:Delivery', template: '@UiComponents/Checkout/Delivery/Delivery.html.twig')]
class Delivery
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?int $deliveryModuleId = null;

    #[LiveProp]
    public ?int $deliveryAddressId = null;

    #[LiveProp]
    public ?int $invoiceAddressId = null;

    public function __construct(
        private readonly ShippingFacade $shippingFacade,
        private readonly CartFacade $cartFacade,
        private readonly CheckoutFacade $checkoutFacade,
        private readonly DeliveryPostageQuerier $postageQuerier,
    ) {
    }

    public function mount(): void
    {
        $this->deliveryAddressId = $this->cartFacade->getDeliveryAddressId();
        $this->invoiceAddressId = $this->cartFacade->getInvoiceAddressId();
    }

    public function getDeliveryModulesOptions(): array
    {
        $cart = $this->cartFacade->getOrCreateFromSession();
        $deliveryModulesWithOption = $this->shippingFacade->listValidMethods($cart);
        $deliveryOptions = [];

        foreach ($deliveryModulesWithOption as $deliveryModuleWithOptionDTO) {
            $options = $deliveryModuleWithOptionDTO->getDeliveryModuleOption();
            $module = $deliveryModuleWithOptionDTO->getDeliveryModule();
            /** @var DeliveryModuleOption $option */
            foreach ($options as $option) {
                $code = $option->getCode();
                $deliveryOptions[$code] = [
                    'code' => $code,
                    'title' => $option->getTitle(),
                    'moduleId' => $module->getId(),
                    'deliveryMode' => $module->getDeliveryMode(),
                    'postage' => $option->getPostage(),
                ];
            }
        }

        return $deliveryOptions;
    }

    public function getCart(): array
    {
        $cart = $this->cartFacade->getOrCreateFromSession();
        $items = $cart->getCartItems();

        return [
            ...$cart->toArray(TableMap::TYPE_CAMELNAME),
            'items' => $items->toArray(null, false, TableMap::TYPE_CAMELNAME),
            'totalItems' => $cart->countCartItems(),
        ];
    }

    #[LiveListener(CheckoutEvents::SET_DELIVERY_MODULE_OPTION)]
    public function selectDeliveryModuleOption(#[LiveArg] string $optionCode, #[LiveArg] int $moduleId): void
    {
        $this->cartFacade->setDeliveryAddress(new CheckoutDTO($this->cartFacade->getOrCreateFromSession()));
        $this->deliveryAddressId = null;
        $this->invoiceAddressId = null;

        $this->cartFacade->setDeliveryModule(new CheckoutDTO(
            cart: $this->cartFacade->getOrCreateFromSession(),
            deliveryModuleId: $moduleId,
        ));
    }

    #[LiveListener(CheckoutEvents::SET_DELIVERY_ORDER_ADDRESS_ID)]
    public function selectDeliveryAddress(#[LiveArg] int $addressId): void
    {
        $this->cartFacade->setDeliveryAddress(new CheckoutDTO(
            cart: $this->cartFacade->getOrCreateFromSession(),
            deliveryAddressId: $addressId,
        ));
        $this->deliveryAddressId = $this->cartFacade->getDeliveryAddressId();
    }

    #[LiveListener(CheckoutEvents::SET_INVOICE_ORDER_ADDRESS_ID)]
    public function selectInvoiceAddress(#[LiveArg] ?int $addressId): void
    {
        $this->cartFacade->setInvoiceAddress(new CheckoutDTO(
            cart: $this->cartFacade->getOrCreateFromSession(),
            invoiceAddressId: $addressId,
        ));
        $this->invoiceAddressId = $this->cartFacade->getInvoiceAddressId();

    }
}
