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

namespace FlexyBundle\Components\Organisms\DeliveryTracking;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Model\OrderStatus;

#[AsTwigComponent]
class Base
{
    private const STEPS = [
        'Order validated',
        'Order in preparation',
        'Order shipped',
        'Order delivered',
    ];

    /**
     * The core knows six statuses and none of them means "delivered", so the last step
     * stays out of reach. Cancelled and refunded orders have no place on a linear
     * tracker: the caller is expected to hide the component for those.
     */
    private const STEP_BY_STATUS = [
        OrderStatus::CODE_NOT_PAID => 0,
        OrderStatus::CODE_PAID => 1,
        OrderStatus::CODE_PROCESSING => 1,
        OrderStatus::CODE_SENT => 2,
    ];

    public ?string $statusCode = null;

    /** Carrier tracking number, when the shop filled one in. */
    public ?string $trackingRef = null;

    /** No carrier tracking URL exists in the core: a delivery module has to provide it. */
    public ?string $trackLink = null;

    /**
     * @return array<int, string>
     */
    public function getSteps(): array
    {
        return self::STEPS;
    }

    public function getCurrentStep(): int
    {
        return self::STEP_BY_STATUS[$this->statusCode] ?? 0;
    }
}
