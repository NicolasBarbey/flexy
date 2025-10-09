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

namespace FlexyBundle\UiComponents\Checkout\Summary;

use FlexyBundle\UiComponents\Checkout\CheckoutEvents;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Api\Service\DataAccess\AttributeAccessService;

#[AsLiveComponent(name: 'Flexy:Checkout:MiniSummary', template: '@UiComponents/Checkout/Summary/Mini.html.twig')]
class MiniSummary extends Summary
{
}
