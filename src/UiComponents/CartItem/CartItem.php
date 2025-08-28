<?php

namespace FlexyBundle\UiComponents\CartItem;

use FlexyBundle\Service\ProductSaleElementsService;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Domain\Localization\Service\LangService;
use Thelia\Domain\Taxation\TaxEngine\TaxEngine;
use Thelia\Model\ProductSaleElementsProductImage;
use Thelia\Model\CartItemQuery;
use Thelia\Model\ProductImage;

#[AsTwigComponent(name: "Flexy:CartItem", template: '@UiComponents/CartItem/CartItem.html.twig')]
class CartItem extends BaseFrontController
{
    public string $cartItemId;
    public bool $outOfStock;
    public string $title;
    public string $secondaryTitle;
    public string $url;
    public ?string $pseImageId;
    public bool $hide = false;
    public ?array $attributesAv = null;
    public int $quantity;
    public ?array $prices = null;

    public function __construct(
        private ProductSaleElementsService $pseService,
        private TaxEngine $taxEngine,
        private LangService $langService,
    ) {}

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
        $this->quantity = $cartItemModel->getQuantity();

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
