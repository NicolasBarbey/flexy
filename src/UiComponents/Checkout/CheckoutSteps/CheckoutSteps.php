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

namespace FlexyBundle\UiComponents\Checkout\CheckoutSteps;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'Flexy:Checkout:CheckoutSteps', template: '@UiComponents/Checkout/CheckoutSteps/CheckoutSteps.html.twig')]
class CheckoutSteps
{
    public const CART = 1;
    public const DELIVERY = 2;
    public const PAYMENT = 3;
    public const GATEWAY = 3;
    public const FAILED = 3;
    public const CONFIRM = 4;
}
