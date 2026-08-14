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

namespace FlexyBundle\Components\Organisms\CartItem;

use FlexyBundle\DTO\CartItemDto;
use FlexyBundle\Service\CartStockService;
use FlexyBundle\Service\ProductSaleElementsService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Domain\Localization\Service\LangService;
use Thelia\Domain\Taxation\TaxEngine\TaxEngine;
use Thelia\Model\CartItemQuery;
use Thelia\Model\ProductImageQuery;
use Thelia\Model\ProductSaleElementsProductImageQuery;

#[AsTwigComponent]
class Base
{
    public CartItemDto $cartItem;
    public bool $outOfStock = false;
    public bool $insufficientStock = false;
    public bool $promo = false;
    public ?int $pseImageId = null;
    public array $prices = [];
    public string $title = '';
    public ?string $desc = '';
    public string $url = '';
    public array $attributesAv = [];

    public function __construct(
        private readonly ProductSaleElementsService $pseService,
        private readonly TaxEngine $taxEngine,
        private readonly LangService $langService,
        private readonly CartStockService $cartStockService,
    ) {
    }

    public function mount(CartItemDto $cartItem): void
    {
        $this->cartItem = $cartItem;

        $cartItemModel = CartItemQuery::create()->findPk($cartItem->id);

        if (null === $cartItemModel) {
            return;
        }

        $pse = $cartItemModel->getProductSaleElements();
        $product = $cartItemModel->getProduct();
        $taxCountry = $this->taxEngine->getDeliveryCountry();

        $this->prices = [
            'taxedPrice' => $cartItemModel->getTaxedPrice($taxCountry),
            'promoTaxedPrice' => $cartItemModel->getTaxedPromoPrice($taxCountry),
        ];

        $this->promo = (bool) $cartItemModel->getPromo();
        $this->title = $product->getTitle();
        $this->desc = $product->getChapo();
        $this->url = $product->getUrl($this->langService->getLocale());
        $this->outOfStock = $this->cartStockService->isOutOfStock($cartItem);
        $this->insufficientStock = $this->cartStockService->isInsufficient($cartItem);
        $this->attributesAv = $this->pseService->getAttributesAvFromPse($pse);

        // getImages() renders visible images only, so an image hidden by the merchant must
        // not win the slot here: it would come back as the placeholder.
        $pseImageId = ProductSaleElementsProductImageQuery::create()
            ->filterByProductSaleElementsId($pse->getId())
            ->useProductImageQuery()
                ->filterByVisible(true)
                ->orderByPosition()
            ->endUse()
            ->findOne()
            ?->getProductImageId();

        if (null !== $pseImageId) {
            $this->pseImageId = $pseImageId;

            return;
        }

        $this->pseImageId = ProductImageQuery::create()
            ->filterByProductId($product->getId())
            ->filterByVisible(true)
            ->orderByPosition()
            ->findOne()
            ?->getId();
    }
}
