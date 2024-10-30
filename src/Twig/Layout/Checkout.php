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

namespace FlexyBundle\Twig\Layout;

use Propel\Runtime\Map\TableMap;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Service\Model\CartService;
use TwigEngine\Service\DataAccess\DataAccessService;

#[AsTwigComponent(template: '@components/Layout/Checkout/Checkout.html.twig')]
class Checkout
{
    public array $cart;

    public function __construct(private DataAccessService $dataAccessService, private CartService $cartService)
    {
    }

    public function getCart(): array
    {
        $sessionCart = $this->cartService->getCart();

        $items = $sessionCart->getCartItems();

        return [...$sessionCart->toArray(TableMap::TYPE_CAMELNAME), 'items' => $items->toArray(null, false, TableMap::TYPE_CAMELNAME)];
    }
}
