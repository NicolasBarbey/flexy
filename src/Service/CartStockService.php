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

namespace FlexyBundle\Service;

use FlexyBundle\DTO\CartItemDto;
use Thelia\Model\Cart;
use Thelia\Model\ConfigQuery;
use Thelia\Model\ProductSaleElements;

/**
 * The core only re-checks stock when the order changes status, i.e. once the order row already
 * exists, and it reports the shortage as a raw developer string (`Thelia\Action\Order`). Reading
 * the same rule up front lets the checkout stop before that point.
 */
final readonly class CartStockService
{
    public function isStockManaged(ProductSaleElements $pse): bool
    {
        return ConfigQuery::checkAvailableStock() && 0 === $pse->getProduct()->getVirtual();
    }

    /**
     * Nothing left: the line cannot be ordered at all.
     */
    public function isOutOfStock(CartItemDto $item): bool
    {
        return $item->stockManaged && $item->stock <= 0;
    }

    /**
     * Some left, but fewer than the line asks for: the quantity has to come down.
     */
    public function isInsufficient(CartItemDto $item): bool
    {
        return $item->stockManaged && $item->stock > 0 && $item->quantity > $item->stock;
    }

    public function hasInsufficientStock(Cart $cart): bool
    {
        foreach ($cart->getCartItems() as $cartItem) {
            $pse = $cartItem->getProductSaleElements();

            if (null === $pse || !$this->isStockManaged($pse)) {
                continue;
            }

            if ($cartItem->getQuantity() > $pse->getQuantity()) {
                return true;
            }
        }

        return false;
    }
}
