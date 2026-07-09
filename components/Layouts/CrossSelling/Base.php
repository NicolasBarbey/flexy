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

namespace FlexyBundle\Components\Layouts\CrossSelling;

use FlexyBundle\DTO\ProductDTO;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Api\Service\DataAccess\DataAccessService;

#[AsTwigComponent]
class Base
{
    public int|string|null $categoryId = null;
    public int $itemsPerPage = 4;

    /** @var ProductDTO[] */
    public array $products = [];

    public function __construct(
        private readonly DataAccessService $dataAccessService,
    ) {
    }

    public function mount(): void
    {
        $params = [
            'page' => 1,
            'itemsPerPage' => $this->itemsPerPage,
            'visible' => true,
        ];

        if ($this->categoryId !== null) {
            $params['productCategories.category.id'] = $this->categoryId;
        }

        $this->products = ProductDTO::fromCollection(
            $this->dataAccessService->resources('/api/front/products', $params) ?? [],
        );
    }
}
