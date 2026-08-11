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
use FlexyBundle\DTO\ProductSaleElementDTO;
use FlexyBundle\Service\ProductImageResolver;
use FlexyBundle\Service\ProductTaxationResolver;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;
use Thelia\Api\Service\DataAccess\DataAccessService;

#[AsTwigComponent]
class Base extends AbstractProductCard
{
    public ?int $productId = null;
    private ?float $price = null;
    private ?float $taxedPrice = null;
    private ?float $promoPrice = null;
    private ?float $promoTaxedPrice = null;
    private bool $isPromo = false;
    private bool $isNew = false;

    public function __construct(
        DataAccessService $dataAccessService,
        ProductImageResolver $productImageResolver,
        private readonly ProductTaxationResolver $productTaxationResolver,
    ) {
        parent::__construct($dataAccessService, $productImageResolver);
    }

    #[PreMount]
    public function preMount(?array $data): void
    {
        if (isset($data['productId']) && $data['productId']) {
            $this->productId = (int) $data['productId'];
        }
    }

    public function mount(ProductDTO|array|null $product = null): void
    {
        $this->loadProduct($product, $this->productId);

        if ($this->product === null) {
            return;
        }

        $this->loadProductImageId();

        $defaultPse = $this->findDefaultPse($this->product->productSaleElements);

        if ($defaultPse instanceof ProductSaleElementDTO) {
            //TODO: temporary fix taxed prices — the front API exposes untaxed prices only
            $this->price = $defaultPse->productPrices[0]->price;
            $this->taxedPrice = $this->productTaxationResolver->taxedPrice($this->product->id, $this->price);
            $this->promoPrice = $defaultPse->productPrices[0]->promoPrice;
            $this->promoTaxedPrice = $this->productTaxationResolver->taxedPrice($this->product->id, $this->promoPrice);
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

    public function getPromoRate(): float
    {
        if ($this->isPromo) {
            return (($this->price - $this->promoPrice) / $this->price) * -1;
        }

        return 0;
    }

    /**
     * @var pseList ProductSaleElementDTO[]
     */
    private function findDefaultPse($pseList): ?ProductSaleElementDTO
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
}
