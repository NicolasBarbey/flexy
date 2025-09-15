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

namespace FlexyBundle\UiComponents\Checkout\Payment;

use FlexyBundle\UiComponents\Checkout\CheckoutEvents;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Api\Service\DataAccess\DataAccessService;
use Thelia\Domain\Cart\CartFacade;
use Thelia\Domain\Checkout\CheckoutFacade;
use Thelia\Domain\Checkout\DTO\CheckoutDTO;
use Thelia\Api\Service\DataAccess\AttributeAccessService;

#[AsLiveComponent(name: 'Flexy:Checkout:Payment', template: '@UiComponents/Checkout/Payment/Payment.html.twig')]
class Payment extends AbstractController
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?int $paymentModuleId = null;

    #[LiveProp]
    public ?int $invoiceAddressId = null;

    public function __construct(
        private readonly DataAccessService $dataAccessService,
        private readonly AttributeAccessService $attributeAccessService,
        private readonly CartFacade $cartFacade,
        private readonly CheckoutFacade $checkoutFacade,
    ) {
    }

    public function mount(): void
    {
        $this->invoiceAddressId = $this->cartFacade->getInvoiceAddressId();
        $this->paymentModuleId = $this->cartFacade->getPaymentModuleId();
    }

    public function getModules(): array
    {
        return $this->dataAccessService->resources('/api/front/payment/modules');
    }

    #[LiveListener(CheckoutEvents::SET_PAYMENT_MODULE_ID)]
    public function selectPaymentModuleId(#[LiveArg] int $moduleId): void
    {
        $this->cartFacade->setPaymentModule(new CheckoutDTO(
            cart: $this->cartFacade->getOrCreateFromSession(),
            paymentModuleId: $moduleId,
        ));

        $this->paymentModuleId = $this->cartFacade->getPaymentModuleId();
    }

    #[LiveListener('submitCart')]
    public function submitCart(): ?Response
    {
        $response = $this->checkoutFacade->pay(new CheckoutDTO(
            cart: $this->cartFacade->getOrCreateFromSession(),
            deliveryModuleId: $this->cartFacade->getDeliveryModuleId(),
            deliveryAddressId: $this->cartFacade->getDeliveryAddressId(),
            invoiceAddressId: $this->cartFacade->getInvoiceAddressId(),
            paymentModuleId: $this->cartFacade->getPaymentModuleId(),
        ));

        if ($response !== null) {
            return $response;
        }
    }
}
