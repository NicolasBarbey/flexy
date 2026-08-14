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

namespace FlexyBundle\Components\Organisms\ProductCard;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Compact tile for the search suggestions. Extends Base, not AbstractProductCard: same data, only
 * the rendering differs (Order starts from the abstract because it comes from an order line).
 */
#[AsTwigComponent]
class Search extends Base
{
}
