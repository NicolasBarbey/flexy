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

namespace FlexyBundle\UiComponents\Checkout;

use Propel\Runtime\Map\TableMap;
use Thelia\Service\Model\CartService;
use TwigEngine\Service\DataAccess\AttributeAccessService;


trait CartManipulationTrait
{

    /** @var CartService */
    protected $cartService;

    /** @var AttributeAccessService */
    protected $attributeAccessService;

    /** @required */
    public function injectCartService(CartService $cartService)
    {
        $this->cartService = $this->cartService ?: $cartService;
    }
    /** @required */
    public function injectAttributeAccessService(AttributeAccessService $attributeAccessService)
    {
        $this->attributeAccessService = $this->attributeAccessService ?: $attributeAccessService;
    }

    public const UPDATE_ITEM_QUANTITY_EVENT = 'UPDATE_ITEM_QUANTITY_EVENT';
    public const ADD_ITEM_EVENT = 'CART_ADD_ITEM_EVENT';
    public const DELETE_ITEM_EVENT = 'CART_DELETE_ITEM_EVENT';

    public function getCart()
    {
        $sessionCart = $this->cartService->getCart();
        $items = $sessionCart->getCartItems();

        return [
            ...$sessionCart->toArray(TableMap::TYPE_CAMELNAME),
            'items' => $items->toArray(null, false, TableMap::TYPE_CAMELNAME),
            'totalItems' => \count($items),
        ];
    }

    public function getSummary()
    {
        return [
            'item_count' =>  $this->attributeAccessService->attributeCart('item_count'),
            'raw_taxed_total_price' => $this->attributeAccessService->attributeCart('raw_taxed_total_price'),
            'total_taxed_price' => $this->attributeAccessService->attributeCart('total_taxed_price'),
            'total_tax_amount' => $this->attributeAccessService->attributeCart('total_tax_amount'),
            'taxed_postage' => $this->attributeAccessService->attributeCart('taxed_postage'),
        ];
    }
}
