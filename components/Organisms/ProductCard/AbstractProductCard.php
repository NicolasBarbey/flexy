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

use FlexyBundle\DTO\ProductDTO;
use FlexyBundle\Service\ProductImageResolver;
use Thelia\Api\Service\DataAccess\DataAccessService;

abstract class AbstractProductCard
{
    protected ?ProductDTO $product = null;
    protected ?int $imageId = null;

    public function __construct(
        protected readonly DataAccessService $dataAccessService,
        protected readonly ProductImageResolver $productImageResolver,
    ) {}

    public function getImageId(): ?int
    {
        return $this->imageId;
    }

    protected function loadProductImageId(): void
    {
        if (null === $this->product?->id) {
            return;
        }

        // Warm from the listing's preload when there is one, single call otherwise.
        $this->imageId = $this->productImageResolver->firstImageId($this->product->id);
    }

    protected function loadProduct(ProductDTO|array|null $product, ?int $productId): void
    {
        if ($product instanceof ProductDTO) {
            $this->product = $product;
            return;
        }

        if (is_array($product)) {
            $this->product = ProductDTO::fromArray($product);
            return;
        }

        if ($productId !== null) {
            $data = $this->dataAccessService->resources('/api/front/products/' . $productId);
            if ($data) {
                $this->product = ProductDTO::fromArray($data);
            }
        }
    }

    public function getProduct(): ?ProductDTO
    {
        return $this->product;
    }
}
