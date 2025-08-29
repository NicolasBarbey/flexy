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

namespace FlexyBundle\UiComponents\OrderCreator;

use FlexyBundle\Form\CheckoutForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Domain\Cart\CartFacade;
use Thelia\Domain\Checkout\CheckoutFacade;
use Thelia\Domain\Checkout\DTO\CheckoutDTO;

#[AsLiveComponent(name: 'Flexy:OrderCreator', template: '@UiComponents/OrderCreator/OrderCreator.html.twig')]
class OrderCreator extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    public function __construct(
        private readonly CartFacade $cartFacade,
        private readonly CheckoutFacade $checkoutFacade,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        $cart = $this->cartFacade->getOrCreateFromSession();

        $data = [
            'delivery-module-id' => $cart->getDeliveryModuleId(),
            'payment-module-id' => $cart->getPaymentModuleId(),
            'delivery-address-id' => $cart->getAddressDeliveryId(),
            'invoice-address-id' => $cart->getAddressInvoiceId(),
        ];

        return $this->createForm(CheckoutForm::class, $data);
    }

    #[LiveAction]
    public function save(): ?Response
    {
        $this->submitForm();

        if ($this->getForm()->isValid()) {
            $formData = $this->getForm()->getData();

            $response = $this->checkoutFacade->pay(
                new CheckoutDTO(
                    cart: $this->cartFacade->getOrCreateFromSession(),
                    deliveryModuleId: $formData['delivery-module-id'],
                    deliveryAddressId: $formData['delivery-address-id'],
                    invoiceAddressId: $formData['invoice-address-id'],
                    paymentModuleId: $formData['payment-module-id']
                )
            );

            if ($response instanceof Response && $response->getStatusCode() === 200) {
                return $response;
            }

            $this->emit('navigateToConfirm');
        }

        return null;
    }
}
