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

namespace FlexyBundle\Components\Layouts\Subheader;

use FlexyBundle\Service\ProductSearch;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Search
{
    public string $query = '';
    public int $productCount = 0;

    public function __construct(
        private readonly ProductSearch $productSearch,
    ) {
    }

    /**
     * The count is queried here rather than read off the listing below, which has not rendered
     * yet — one extra query, and the number is right without JavaScript.
     */
    public function mount(string $query = ''): void
    {
        $this->query = $query;
        $this->productCount = $this->productSearch->count($query);
    }
}
