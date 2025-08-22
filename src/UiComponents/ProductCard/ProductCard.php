<?php

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

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use TwigEngine\Service\DataAccess\DataAccessService;

#[AsTwigComponent(name: "Flexy:ProductCard", template: '@UiComponents/ProductCard/ProductCard.html.twig')]
class ProductCard
{
    private DataAccessService $dataAccessService;
    public ?string $productId = '';

    #[ExposeInTemplate]
    public ?array $product = null;

    public function __construct(DataAccessService $dataAccessService)
    {
        $this->dataAccessService = $dataAccessService;
    }

    public function getProduct()
    {
        if (null !== $this->product) {
            return $this->product;
        }

        if ('' === $this->productId) {
            return null;
        }

        $this->product = $this->dataAccessService->resources('/api/front/products/' . $this->productId);

        return $this->product;
    }
}
