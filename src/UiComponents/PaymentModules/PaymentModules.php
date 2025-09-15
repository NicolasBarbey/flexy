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

namespace FlexyBundle\UiComponents\PaymentModules;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Domain\Addressing\Service\AddressService;
use Thelia\Domain\Cart\CartFacade;
use Thelia\Domain\Checkout\DTO\CheckoutDTO;
use Thelia\Api\Service\DataAccess\DataAccessService;

#[AsLiveComponent(name: 'Flexy:PaymentModules', template: '@UiComponents/PaymentModules/PaymentModules.html.twig')]
class PaymentModules extends BaseFrontController
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp()]
    public ?array $modules = [];

    #[LiveProp(writable: true, onUpdated: 'setCartPaymentModuleId')]
    public ?int $paymentModuleId = null;

    #[LiveProp]
    public ?int $invoiceAddressId = null;

    public function __construct(
        private readonly Session $session,
        private readonly AddressService $addressService,
        private readonly CartFacade $cartFacade,
        private DataAccessService $dataAccessService,
    ) {
    }

    public function mount(): void
    {
        $cart = $this->cartFacade->getOrCreateFromSession();
        $this->modules = $this->dataAccessService->resources('/api/front/payment/modules');
        $this->invoiceAddressId = $cart->getAddressInvoiceId();
    }

    #[LiveAction]
    public function setCartPaymentModuleId(): void
    {
        if ($this->paymentModuleId) {
            $this->cartFacade->setPaymentModule(
                new CheckoutDTO(
                    cart: $this->cartFacade->getOrCreateFromSession(),
                    paymentModuleId: $this->paymentModuleId
                )
            );
            $this->emit('resetCart');
        }
    }
}
