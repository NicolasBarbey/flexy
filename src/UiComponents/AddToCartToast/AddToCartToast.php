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

namespace FlexyBundle\UiComponents\AddToCartToast;

use FlexyBundle\Service\ProductSaleElementsService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Model\Base\ProductSaleElementsProductImageQuery;
use Thelia\Model\ProductImageQuery;
use Thelia\Model\ProductSaleElementsQuery;

#[AsLiveComponent(name: 'Flexy:AddToCartToast', template: '@UiComponents/AddToCartToast/AddToCartToast.html.twig')]
class AddToCartToast extends BaseFrontController
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?int $quantity = null;
    #[LiveProp]
    public ?int $pseId = null;
    #[LiveProp]
    public ?string $title = null;
    #[LiveProp]
    public ?int $productId = null;
    #[LiveProp]
    public ?string $secondaryTitle = null;
    #[LiveProp]
    public ?int $imageId = null;

    #[LiveProp]
    public ?array $attributesAv = null;

    public function __construct(private ProductSaleElementsService $pseService)
    {
    }

    #[LiveListener('addToCart')]
    public function addToCart(#[LiveArg] array $values): void
    {
        $this->quantity = (int) $values['quantity'];
        $this->pseId = (int) $values['product_sale_elements_id'];
        $locale = $this->requestStack->getCurrentRequest()->getSession()->getLang()->getLocale();
        $pse = ProductSaleElementsQuery::create()->findPk($this->pseId);
        $product = $pse->getProduct()->setLocale($locale);
        $this->title = $product->getTitle();
        $this->secondaryTitle = $product->getChapo();
        $this->productId = $product->getId();

        $this->imageId = ProductSaleElementsProductImageQuery::create()->filterByProductSaleElementsId($this->pseId)->findOne()?->getProductImageId();
        if ($this->imageId === null) {
            $this->imageId = ProductImageQuery::create()->filterByProductId($this->productId)->orderByPosition()->findOne()?->getId();
        }
        $this->attributesAv = $this->pseService->getAttributesAvFromPse($pse);
    }

    #[LiveAction]
    public function closeToast(): void
    {
        $this->resetValues();
    }

    protected function resetValues(): void
    {
        $this->quantity = null;
        $this->pseId = null;
        $this->productId = null;
        $this->title = null;
        $this->secondaryTitle = null;
        $this->attributesAv = null;
    }
}
