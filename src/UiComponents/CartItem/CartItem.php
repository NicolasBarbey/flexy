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

namespace FlexyBundle\UiComponents\CartItem;

use FlexyBundle\Service\ProductSaleElementsService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Domain\Localization\Service\LangService;
use Thelia\Domain\Taxation\TaxEngine\TaxEngine;
use Thelia\Model\CartItemQuery;
use Thelia\Model\ProductImage;
use Thelia\Model\ProductSaleElementsProductImage;

#[AsTwigComponent(name: 'Flexy:CartItem', template: '@UiComponents/CartItem/CartItem.html.twig')]
class CartItem extends BaseFrontController
{
    public string $cartItemId;
    public bool $outOfStock;
    public string $title;
    public string $secondaryTitle;
    public string $url;
    public ?int $pseImageId;
    public bool $hide = false;
    public ?array $attributesAv = null;
    public int $quantity;
    public ?array $prices = null;

    public function __construct(
        private ProductSaleElementsService $pseService,
        private TaxEngine $taxEngine,
        private LangService $langService,
    ) {
    }

    public function mount(string $cartItemId): void
    {
        // TODO: Move to a service
        $this->cartItemId = $cartItemId;
        $cartItemModel = CartItemQuery::create()->findPk($cartItemId);
        $product = $cartItemModel->getProduct();
        $productSaleElement = $cartItemModel->getProductSaleElements();
        $taxCountry = $this->taxEngine->getDeliveryCountry();

        $this->prices['price'] = $cartItemModel->getPrice();
        $this->prices['promoPrice'] = $cartItemModel->getPromoPrice();
        $this->prices['taxedPrice'] = $cartItemModel->getTaxedPrice($taxCountry);
        $this->prices['promoTaxedPrice'] = $cartItemModel->getTaxedPromoPrice($taxCountry);
        $this->prices['promo'] = $cartItemModel->getPromo() ? true : false;

        $this->outOfStock = $cartItemModel->getQuantity() <= 0;
        $this->quantity = (int) $cartItemModel->getQuantity();

        $this->title = $product->getTitle();
        $this->secondaryTitle = $product->getChapo();

        $locale = $this->langService->getLocale();
        $this->url = $product->getUrl($locale);

        /** @var ProductSaleElementsProductImage $pseImage */
        $pseImage = $productSaleElement->getProductSaleElementsProductImages()->getFirst();

        if ($pseImage) {
            $this->pseImageId = $pseImage->getProductImageId();

            return;
        }
        /** @var ProductImage $productImage */
        $productImage = $cartItemModel->getProduct()->getProductImages()->getFirst();

        if ($productImage) {
            $this->pseImageId = $productImage->getId();
        }

        $this->attributesAv = $this->pseService->getAttributesAvFromPse($cartItemModel->getProductSaleElements());
    }
}
