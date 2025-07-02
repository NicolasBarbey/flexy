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

namespace FlexyBundle\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Controller\Front\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;

#[Route('/checkout', name: 'checkout_')]
class CheckoutController extends BaseFrontController
{
    public const STEP_CART = 'cart';
    public const STEP_DELIVERY = 'delivery';
    public const STEP_PAYMENT = 'payment';
    public const STEP_GATEWAY = 'gateway';
    public const STEP_CONFIRM = 'confirm';
    public const STEPS = [
        self::STEP_CART => 1,
        self::STEP_DELIVERY => 2,
        self::STEP_PAYMENT => 3,
        self::STEP_GATEWAY => 3,
        self::STEP_CONFIRM => 4,
    ];

    #[Route('', name: 'no_route')]
    public function noRouteAction(): Response
    {
        return $this->pageNotFound();
    }

    #[Route('/cart', name: 'cart')]
    public function cartAction(): Response
    {
        return $this->render('checkout', [
            'page' => self::STEP_CART,
            'current' => self::STEPS[self::STEP_CART],
        ]);
    }

    #[Route('/delivery', name: 'delivery')]
    public function deliveryAction(EventDispatcherInterface $eventDispatcher): Response
    {
        $this->checkAuth();
        $this->checkCartNotEmpty($eventDispatcher);

        return $this->render('checkout', [
            'page' => self::STEP_DELIVERY,
            'current' => self::STEPS[self::STEP_DELIVERY],
        ]);
    }

    #[Route('/payment', name: 'payment')]
    public function paymentAction(EventDispatcherInterface $eventDispatcher): Response
    {
        $this->checkAuth();
        $this->checkCartNotEmpty($eventDispatcher);

        // TODO le paiment n'est accessible que lorsqu'on a une adresse de livraison et un module dans le cart

        return $this->render('checkout', [
            'page' => self::STEP_PAYMENT,
            'current' => self::STEPS[self::STEP_PAYMENT],
        ]);
    }

    #[Route('/gateway', name: 'gateway')]
    public function gatewayAction(EventDispatcherInterface $eventDispatcher): Response
    {
        $this->checkAuth();
        $this->checkCartNotEmpty($eventDispatcher);

        // TODO page affiché en attendant la liasion avec le module de payment

        return $this->render('checkout', [
            'page' => self::STEP_GATEWAY,
            'current' => self::STEPS[self::STEP_GATEWAY],
        ]);
    }

    #[Route('/confirm', name: 'confirm')]
    public function confirmAction(EventDispatcherInterface $eventDispatcher): Response
    {
        $this->checkAuth();
        $this->checkCartNotEmpty($eventDispatcher);

        return $this->render('checkout', [
            'page' => self::STEP_CONFIRM,
            'current' => self::STEPS[self::STEP_CONFIRM],
        ]);
    }
}
