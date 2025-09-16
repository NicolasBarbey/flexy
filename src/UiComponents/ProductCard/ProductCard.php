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

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Attribute\PreMount;
use Thelia\Api\Service\DataAccess\DataAccessService;

#[AsTwigComponent(name: 'Flexy:ProductCard', template: '@UiComponents/ProductCard/ProductCard.html.twig')]
class ProductCard
{
    private DataAccessService $dataAccessService;
    public ?int $productId = null;
    private ?array $product = null;



    public function __construct(DataAccessService $dataAccessService)
    {
        $this->dataAccessService = $dataAccessService;
    }

    #[PreMount]
    public function preMount(?array $data): void
    {

        if (isset($data['productId']) && $data['productId']) {
            $this->productId = $data['productId'];
        }
    }

    public function mount(?array $product = null): void
    {
        if (isset($product) && $product) {
            $this->product = $product;
        }

        if ($this->productId === null && $this->product === null) {
            return;
        }

        if ($this->productId !== null && $this->product === null) {
            $this->product = $this->dataAccessService->resources('/api/front/products/' . $this->productId);
        }


        $defaultPse = $this->findDefaultPse($this->product['productSaleElements']);

        $this->product['price'] = $defaultPse['productPrices'][0]['price'];
        $this->product['promoPrice'] = $defaultPse['productPrices'][0]['promoPrice'];
        $this->product['isPromo'] = $defaultPse['promo'];
        $this->product['isNew'] = $defaultPse['newness'];



        $this->product = array_merge($this->product, [
            'colors' => [],
            'rate' => null,
        ]);
    }

    private function findDefaultPse(?array $pseList)
    {

        if ($pseList) {
            foreach ($pseList as $pse) {
                if ($pse['isDefault']) {
                    return $pse;
                }
            }
        }

        return null;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function getPromoRate(): float
    {
        if ($this->product['isPromo']) {
            return (($this->product['price'] - $this->product['promoPrice']) / $this->product['price']) * -1;
        }

        return 0;
    }
}
