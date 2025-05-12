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

namespace FlexyBundle\Twig\Organisms;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Thelia\Service\Model\CartService;

#[AsLiveComponent(template: '@components/Organisms/CartItem/CartItemDelete.html.twig')]
class CartItemDelete
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public array $cartItems = [];

    #[ExposeInTemplate]
    public int $timer = 3;

    public function __construct(
        private CartService $cartService
    ) {
    }

    #[LiveListener('removeCartItem')]
    public function showToast(#[LiveArg] int $id, #[LiveArg] string $title, #[LiveArg] ?int $imageId, #[LiveArg] ?int $productId): void
    {
        $this->cartItems[$id] = [
          'id' => $id,
          'title' => $title,
          'imageId' => $imageId,
          'productId' => $productId,
        ];
        $this->dispatchBrowserEvent('cartitem:toast', ['timer' => $this->timer, 'id' => $id]);
    }

    #[LiveAction]
    public function deleteCartItem(#[LiveArg] int $id): void
    {
        if (!$id) {
            return;
        }
        $this->resetValues($id);
        $this->cartService->deleteItem($id);
        // if (count($this->cartItems))
        $this->emit('resetCart');
    }

    #[LiveAction]
    public function cancelDelete(#[LiveArg] $id): void
    {
        $this->emit('cancelDelete', componentName: 'Flexy:Organisms:CartItem');
        $this->resetValues($id);
    }

    protected function resetValues($id): void
    {
        unset($this->cartItems[$id]);
    }
}
