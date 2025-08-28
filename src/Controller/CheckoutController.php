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

namespace FlexyBundle\Controller;

use FlexyBundle\UiComponents\Checkout\CheckoutSteps;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Core\HttpKernel\Exception\RedirectException;
use Thelia\Domain\Cart\CartFacade;
use Thelia\Domain\Cart\Service\CartGuard;
use Thelia\Domain\Checkout\CheckoutFacade;
use Thelia\Domain\Checkout\Exception\EmptyCartException;
use Thelia\Domain\Checkout\Exception\InvalidDeliveryException;
use Thelia\Domain\Checkout\Exception\MissingAddressException;
use Thelia\Domain\Shipping\ShippingFacade;

#[Route('/checkout', name: 'checkout_')]
class CheckoutController extends FlexyController
{

    #[Route('', name: 'no_route')]
    public function noRouteAction(): Response
    {
        return $this->generateRedirect('/checkout/cart');
        // return $this->pageNotFound();
    }

    #[Route('/cart', name: 'cart')]
    public function cartAction(
        CheckoutFacade $checkoutFacade,
        CartGuard $cartGuard,
        CartFacade $cartFacade
    ): Response
    {
        $cart = $cartFacade->getOrCreateFromSession();
        $checkoutFacade->resetCheckout($cart);
        $emptyCart = false;

        try {
            $cartGuard->checkCartNotEmpty($cart);
        } catch (EmptyCartException) {
            $emptyCart = true;
        }

        return $this->render('checkout-cart', [
            'emptyCart' => $emptyCart,
            'current' => CheckoutSteps::CART,
        ]);
    }

    #[Route('/delivery', name: 'delivery')]
    public function deliveryModesAction(
        CartFacade $cartFacade,
        CartGuard $cartGuard,
        ShippingFacade $shippingFacade
    ): Response
    {
        $this->checkAuth();
        $cart = $cartFacade->getOrCreateFromSession();
        try {
            $cartGuard->checkCartNotEmpty($cart);

            if ($cart->isVirtual()) {
                $shippingFacade->setupVirtualDelivery($cart);
            }

            return $this->render('checkout-delivery', [
                'current' => CheckoutSteps::DELIVERY,
            ]);
        } catch (EmptyCartException $e) {
            throw new RedirectException($this->generateUrl('checkout_cart'), Response::HTTP_FOUND, $e->getMessage());
        }
    }


    #[Route('/payment', name: 'payment')]
    public function paymentAction(
        CartGuard $cartGuard,
        CartFacade $cartFacade,
    ): Response
    {
        $this->checkAuth();
        try {
            $cart = $cartFacade->getOrCreateFromSession();
            $cartGuard->checkCartNotEmpty($cart);
            $cartGuard->checkValidDelivery($cart);

            return $this->render('checkout-payment', [
                'current' => CheckoutSteps::PAYMENT,
            ]);
        } catch (EmptyCartException $e) {
            throw new RedirectException($this->generateUrl('checkout_cart'), Response::HTTP_FOUND, $e->getMessage());
        } catch (MissingAddressException | InvalidDeliveryException $e) {
            throw new RedirectException($this->generateUrl('checkout_delivery'), Response::HTTP_FOUND, $e->getMessage());
        }
    }

    #[Route('/gateway', name: 'gateway')]
    public function gatewayAction(): Response
    {
        $this->checkAuth();

        return $this->render('checkout', [
            'current' => CheckoutSteps::GATEWAY,
        ]);
    }

    #[Route('/confirm', name: 'confirm')]
    public function confirmAction(): Response
    {
        $this->checkAuth();

        return $this->render('checkout', [
            'current' => CheckoutSteps::CONFIRM,
        ]);
    }
}
