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

namespace FlexyBundle\Components\Molecules\Tag;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Model\OrderStatus;

#[AsTwigComponent]
class Order
{
    public function getVariant(string $variant): string
    {
        return match ($variant) {
            OrderStatus::CODE_CANCELED => 'color-grey-lightest',
            OrderStatus::CODE_NOT_PAID => 'color-error',
            OrderStatus::CODE_PAID => 'color-informative',
            OrderStatus::CODE_PROCESSING => 'color-processing',
            OrderStatus::CODE_REFUNDED => 'color-warning',
            OrderStatus::CODE_SENT => 'color-success',
            default => '',
        };
    }
}
