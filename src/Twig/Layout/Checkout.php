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
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Service\Model\CartService;
use TwigEngine\Service\DataAccess\DataAccessService;

#[AsLiveComponent(template: '@components/Layout/Checkout/Checkout.html.twig')]
class Checkout
{
    use DefaultActionTrait;
    #[LiveProp]
    public array $cart;

    public function __construct(
        private DataAccessService $dataAccessService,
        private CartService $cartService
    ) {
    }

    public function mount(): void
    {
        $this->setCart();
    }

    #[LiveListener('removeCartItem')]
    public function removeCartItem(#[LiveArg] string $id): void
    {
        $this->cartService->deleteItem((int) $id);
        $this->setCart();
    }

    public function getCart(): array
    {
        return $this->cart;
    }

    protected function setCart(): void
    {
        $sessionCart = $this->cartService->getCart();

        $items = $sessionCart->getCartItems();

        $this->cart = [...$sessionCart->toArray(TableMap::TYPE_CAMELNAME), 'items' => $items->toArray(null, false, TableMap::TYPE_CAMELNAME)];
    }
}
