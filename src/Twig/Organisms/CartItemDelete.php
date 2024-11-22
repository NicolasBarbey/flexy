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
    public ?int $cartItemId = null;

    #[LiveProp]
    public ?string $title = null;

    #[LiveProp]
    public ?int $imageId = null;

    #[ExposeInTemplate]
    public int $timer = 3;

    public function __construct(
        private CartService $cartService
    ) {
    }

    #[LiveListener('removeCartItem')]
    public function showToast(#[LiveArg] int $id, #[LiveArg] string $title, #[LiveArg] ?int $imageId): void
    {
        $this->cartItemId = $id;
        $this->title = $title;
        $this->imageId = $imageId;
        $this->dispatchBrowserEvent('cartitem:toast', ['timer' => $this->timer]);
    }

    #[LiveAction]
    public function deleteCartItem(): void
    {
        $id = $this->cartItemId;
        if (!$id) {
            return;
        }
        $this->resetValues();
        $this->cartService->deleteItem($id);
        $this->emit('resetCart');
    }

    #[LiveAction]
    public function cancelDelete(): void
    {
        $this->emit('cancelDelete', componentName: 'Flexy:Organisms:CartItem');
        $this->resetValues();
    }

    protected function resetValues(): void
    {
        $this->cartItemId = null;
        $this->title = null;
        $this->imageId = null;
    }
}
