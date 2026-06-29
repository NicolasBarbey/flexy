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

use FlexyBundle\DTO\OrderProductDTO;
use FlexyBundle\DTO\ProductDTO;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Api\Service\DataAccess\DataAccessService;

#[AsTwigComponent]
class Order extends AbstractProductCard
{
    public OrderProductDTO $orderProduct;
    private ?array $pse = null;

    public function __construct(DataAccessService $dataAccessService)
    {
        parent::__construct($dataAccessService);
    }

    public function mount(array $orderProduct, ProductDTO|array|null $product = null, ?array $pse = null): void
    {
        $this->orderProduct = OrderProductDTO::fromArray($orderProduct);
        $this->loadProduct($product, $this->orderProduct->productId);

        $this->pse = $pse ?? $this->dataAccessService->resources(
            '/api/front/product_sale_elements/' . $this->orderProduct->productSaleElementsId
        );
    }

    public function getPse(): ?array
    {
        return $this->pse;
    }
}
