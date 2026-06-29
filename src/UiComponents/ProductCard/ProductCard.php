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

namespace FlexyBundle\UiComponents\ProductCard;

use FlexyBundle\DTO\ProductDTO;
use FlexyBundle\DTO\ProductSaleElementDTO;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;
use Thelia\Api\Service\DataAccess\DataAccessService;
use Thelia\Domain\Taxation\TaxEngine\TaxEngine;
use Thelia\Model\ProductQuery;

#[AsTwigComponent(name: 'Flexy:ProductCard', template: '@UiComponents/ProductCard/ProductCard.html.twig')]
class ProductCard
{
    public ?int $productId = null;
    private ?ProductDTO $product = null;
    private ?int $defaultPseId = null;
    private ?int $imageId = null;
    private ?float $price = null;
    private ?float $taxedPrice = null;
    private ?float $promoPrice = null;
    private ?float $promoTaxedPrice = null;
    private bool $isPromo = false;
    private bool $isNew = false;

    public function __construct(
        private readonly DataAccessService $dataAccessService,
        private TaxEngine $taxEngine,
    ) {
    }

    #[PreMount]
    public function preMount(?array $data): void
    {
        if (isset($data['productId']) && $data['productId']) {
            $this->productId = $data['productId'];
        }
    }

    public function mount(ProductDTO|array|null $product = null): void
    {
        if ($product instanceof ProductDTO) {
            $this->product = $product;
        } elseif (is_array($product)) {
            $this->product = ProductDTO::fromArray($product);
        }

        if ($this->productId === null && $this->product === null) {
            return;
        }

        if ($this->productId !== null && $this->product === null) {
            $data = $this->dataAccessService->resources('/api/front/products/' . $this->productId);
            if (!$data) {
                return;
            }

            $this->product = ProductDTO::fromArray($data);
        }

        $defaultPse = $this->findDefaultPse($this->product->productSaleElements);

        $this->defaultPseId = $defaultPse?->id;

        $this->setImage($defaultPse);

        if ($defaultPse instanceof ProductSaleElementDTO) {
            //TODO: temporary fix taxed prices

            $productModel = ProductQuery::create()
                ->useProductSaleElementsQuery()
                    ->filterById($defaultPse->id)
                ->endUse()
                ->findOne();

            $taxCountry = $this->taxEngine->getDeliveryCountry();

            $this->price = $defaultPse->productPrices[0]->price;
            $this->taxedPrice = $productModel->getTaxedPrice($taxCountry, $this->price);
            $this->promoPrice = $defaultPse->productPrices[0]->promoPrice;
            $this->promoTaxedPrice = $productModel->getTaxedPromoPrice($taxCountry, $this->promoPrice);
            $this->isPromo = $defaultPse->promo;
            $this->isNew = $defaultPse->newness;
        }
    }

    public function getRate()
    {
        return null;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getPromoPrice()
    {
        return $this->promoPrice;
    }

    public function getTaxedPrice()
    {
        return $this->taxedPrice;
    }

    public function getPromoTaxedPrice()
    {
        return $this->promoTaxedPrice;
    }

    public function getIsPromo()
    {
        return $this->isPromo;
    }

    public function getIsNew()
    {
        return $this->isNew;
    }


    /**
     * @var pseList ProductSaleElementDTO[]
     * @return ProductSaleElementDTO | null
     */
    private function findDefaultPse($pseList)
    {

        if ($pseList) {
            foreach ($pseList as $pse) {
                if ($pse->isDefault) {
                    return $pse;
                }
            }
        }

        return null;
    }

    public function getProduct(): ProductDTO
    {
        return $this->product;
    }

    public function getDefaultPseId(): ?int
    {
        if (null !== $this->defaultPseId) {
            return $this->defaultPseId;
        }

        $firstPse = $this->product?->productSaleElements[0] ?? null;

        return $firstPse?->id;
    }

    public function getImageId(): ?int
    {
        return $this->imageId;
    }

    /**
     * Resolve the image to display: the one linked to the default PSE if any,
     * otherwise the first product image.
     */
    private function setImage(?ProductSaleElementDTO $defaultPse): void
    {
        $productImages = $this->dataAccessService->resources(
            '/api/front/product_images',
            [
                'product.id' => $this->product->id,
                'visible' => true,
                'order[position]' => 'asc',
            ]
        );

        if (!$productImages) {
            return;
        }

        if ($defaultPse instanceof ProductSaleElementDTO) {
            $pseImages = $this->dataAccessService->resources(
                '/api/front/product_sale_elements_product_image',
                [
                    'productImageId' => array_map(static fn ($img) => $img['id'], $productImages),
                    'productSaleElements.product.id' => $this->product->id,
                    'visible' => true,
                ]
            );

            $pseImageIds = [];
            foreach ($pseImages as $pseImage) {
                if ((int) $pseImage['productSaleElementsId'] === $defaultPse->id) {
                    $pseImageIds[] = $pseImage['productImageId'];
                }
            }

            foreach ($productImages as $productImage) {
                if (in_array($productImage['id'], $pseImageIds, true)) {
                    $this->imageId = $productImage['id'];

                    return;
                }
            }
        }

        $this->imageId = $productImages[0]['id'] ?? null;
    }

    public function getPromoRate(): float
    {
        if ($this->isPromo) {
            return (($this->price - $this->promoPrice) / $this->price) * -1;
        }

        return 0;
    }
}
