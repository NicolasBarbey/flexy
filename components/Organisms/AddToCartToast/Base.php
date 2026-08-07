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

namespace FlexyBundle\Components\Organisms\AddToCartToast;

use FlexyBundle\Service\ProductSaleElementsService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Domain\Localization\LocalizationFacade;
use Thelia\Model\ProductImageQuery;
use Thelia\Model\ProductSaleElementsProductImageQuery;
use Thelia\Model\ProductSaleElementsQuery;

#[AsLiveComponent]
class Base
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?int $quantity = null;

    #[LiveProp]
    public ?int $pseId = null;

    #[LiveProp]
    public ?string $title = null;

    #[LiveProp]
    public ?string $secondaryTitle = null;

    #[LiveProp]
    public ?string $productUrl = null;

    #[LiveProp]
    public ?int $imageId = null;

    #[LiveProp]
    public array $attributesAv = [];

    public function __construct(
        private readonly ProductSaleElementsService $pseService,
        private readonly LocalizationFacade $localizationFacade,
    ) {
    }

    #[LiveListener('addToCart')]
    public function addToCart(#[LiveArg] array $values): void
    {
        // `$values` travels back through the browser, so the keys are not guaranteed to be there.
        $pse = ProductSaleElementsQuery::create()->findPk((int) ($values['product_sale_elements_id'] ?? 0));

        if ($pse === null) {
            return;
        }

        $locale = $this->localizationFacade->getCurrentLocale();
        $product = $pse->getProduct()->setLocale($locale);

        $this->quantity = max(1, (int) ($values['quantity'] ?? 1));
        $this->pseId = $pse->getId();
        $this->title = $product->getTitle();
        $this->secondaryTitle = $product->getChapo();
        $this->productUrl = $product->getUrl($locale);
        $this->attributesAv = $this->pseService->getAttributesAvFromPse($pse);

        // A PSE-specific visual when it has one, the product's first visual otherwise.
        $this->imageId = ProductSaleElementsProductImageQuery::create()
            ->filterByProductSaleElementsId($this->pseId)
            ->useProductImageQuery()
                ->orderByPosition()
            ->endUse()
            ->findOne()
            ?->getProductImageId()
            ?? ProductImageQuery::create()
                ->filterByProductId($product->getId())
                ->orderByPosition()
                ->findOne()
                ?->getId();
    }

    #[LiveAction]
    public function closeToast(): void
    {
        $this->quantity = null;
        $this->pseId = null;
        $this->title = null;
        $this->secondaryTitle = null;
        $this->productUrl = null;
        $this->imageId = null;
        $this->attributesAv = [];
    }
}
