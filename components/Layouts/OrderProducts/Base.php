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

namespace FlexyBundle\Components\Layouts\OrderProducts;

use FlexyBundle\Service\OrderProductResolver;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Owns the lines of one order so they can be resolved together: a card left to itself
 * costs four API calls, which the page would pay once per line.
 */
#[AsTwigComponent]
class Base
{
    /** @var array<int, array{orderProduct: array<string, mixed>, product: \FlexyBundle\DTO\ProductDTO|null, pse: array<string, mixed>|null, imageId: int|null}> */
    public array $lines = [];

    public function __construct(
        private readonly OrderProductResolver $orderProductResolver,
    ) {
    }

    /**
     * @param array<int, array<string, mixed>> $orderProducts
     */
    public function mount(array $orderProducts = []): void
    {
        $this->lines = $this->orderProductResolver->resolveLines($orderProducts);
    }
}
