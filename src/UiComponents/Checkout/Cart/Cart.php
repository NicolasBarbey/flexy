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

namespace FlexyBundle\UiComponents\Checkout\Cart;

use FlexyBundle\DTO\CartItemDto;
use FlexyBundle\Service\ProductSaleElementsService;
use FlexyBundle\UiComponents\Checkout\CheckoutEvents;
use Propel\Runtime\Map\TableMap;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Domain\Cart\CartFacade;
use Thelia\Domain\Cart\DTO\CartItemAddDTO;
use Thelia\Domain\Cart\DTO\CartItemDeleteDTO;
use Thelia\Domain\Cart\DTO\CartItemUpdateQuantityDTO;
use Thelia\Form\Definition\FrontForm;
use Thelia\Model\ProductImage;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductSaleElementsQuery;
use TwigEngine\Service\FormService;

#[AsLiveComponent(name: 'Flexy:Checkout:Cart', template: '@UiComponents/Checkout/Cart/Cart.html.twig')]
class Cart
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    public ?array $pendingDelete = null;

    /**
     * @var CartItemDto[]
     */
    #[LiveProp(writable: true)]
    public array $items = [];

    public bool $itemHasNoStockMessage = false;

    public function __construct(
        private readonly CartFacade $cartFacade,
        private readonly ProductSaleElementsService $pseService,
        private readonly FormService $formService,
    ) {
    }

    #[LiveListener('cross_selling_add_to_cart')]
    public function sync(): void
    {
        $this->fetchCart();
    }

    public function fetchCart(): void
    {
        $this->items = [];
        $cart = $this->cartFacade->getCartFromSession();
        foreach ($cart->getCartItems()?->toArray(null, false, TableMap::TYPE_CAMELNAME) as $item) {
            $pse = ProductSaleElementsQuery::create()
                ->findOneById($item['productSaleElementsId']);

            $this->items[] = CartItemDto::fromArray(
                [
                    ...$item,
                    'stock' => (int) $pse->getQuantity(),
                    'title' => $pse->getProduct()->getTitle(),
                ]
            );
            if ($pse->getQuantity() <= 0) {
                $this->itemHasNoStockMessage = true;
            }
        }
    }

    public function mount(): void
    {
        $this->fetchCart();
    }

    public function getTotalItems()
    {
        $countAllItem = 0;
        foreach ($this->items as $item) {
            $countAllItem += $item->quantity;
        }

        return $countAllItem;
    }

    private function findCartItemByIndex(int $index): ?CartItemDto
    {
        return $this->items[$index];
    }

    #[LiveListener('changeQuantity')]
    public function onQuantityUpdated(#[LiveArg] int $index, #[LiveArg] int $quantity, #[LiveArg] int $baseQuantity): void
    {
        $cartItem = $this->findCartItemByIndex($index);

        if ($cartItem->id && $quantity <= 0) {
            $this->remove($cartItem->id);

            return;
        }

        if ($quantity > $baseQuantity) {
            $this->plus($index, $quantity);

            return;
        }

        if ($quantity < $baseQuantity) {
            $this->minus($index, $quantity);

            return;
        }
    }

    public function setQuantity(): void
    {
    }

    #[LiveAction]
    public function minus(#[LiveArg] int $index, ?int $quantity): void
    {
        $cartItem = $this->findCartItemByIndex($index);
        $newQuantity = max(0, $quantity ?? $cartItem->quantity - 1);

        if ($newQuantity === 0) {
            $this->remove($index);

            return;
        }

        $this->cartFacade->updateItemQuantity(
            new CartItemUpdateQuantityDTO(
                cart: $this->cartFacade->getOrCreateFromSession(),
                cartItemId: $cartItem->id,
                quantity: $newQuantity,
            )
        );
        $cartItem->quantity = $newQuantity;
        $this->fetchCart();
        $this->emit(CheckoutEvents::UPDATE_ITEM_QUANTITY_EVENT);
    }

    #[LiveAction]
    public function plus(#[LiveArg] int $index, ?int $quantity): void
    {
        $cartItem = $this->findCartItemByIndex($index);

        $newQuantity = min($cartItem->stock, $quantity ?? $cartItem->quantity + 1);

        $this->cartFacade->updateItemQuantity(
            new CartItemUpdateQuantityDTO(
                cart: $this->cartFacade->getOrCreateFromSession(),
                cartItemId: $cartItem->id,
                quantity: $newQuantity,
            )
        );

        $this->fetchCart();
        $this->emit(CheckoutEvents::UPDATE_ITEM_QUANTITY_EVENT);
    }

    #[LiveAction]
    public function remove(#[LiveArg] int $index): void
    {
        $match = $this->findCartItemByIndex($index);
        $this->pendingDelete = [
            'title' => $match->title,
            'productId' => $match->productId,
            'pseId' => $match->productSaleElementsId,
            'quantity' => $match->quantity,
            'imageId' => null,
        ];

        /** @var ProductImage $productImage */
        $productImage = ProductQuery::create()->findOneById($match->productId);

        if ($productImage) {
            $this->pendingDelete['imageId'] = $productImage->getId();
        }

        $pseImage = ProductSaleElementsQuery::create()->findOneById($match->productSaleElementsId)->getProductSaleElementsProductImages()->getFirst();

        if ($pseImage) {
            $this->pendingDelete['imageId'] = $pseImage->getProductImageId();
        }

        $this->cartFacade->removeItem(new CartItemDeleteDTO(
            cart: $this->cartFacade->getOrCreateFromSession(),
            cartItemId: $match->id,
        ));
        $this->fetchCart();
        $this->emit(CheckoutEvents::DELETE_ITEM_EVENT);
    }

    #[LiveAction]
    public function restoreCartItem(#[LiveArg] int $pseId, #[LiveArg] int $productId, #[LiveArg] ?int $quantity): void
    {
        if (!$pseId || !$productId) {
            return;
        }
        $cart = $this->cartFacade->getCartFromSession();
        $form = $this->formService->getFormByName(FrontForm::CART_ADD);

        $form->submit([
            'product' => $productId,
            'product_sale_elements_id' => $pseId,
            'quantity' => $quantity ?? 1,
            'append' => 1,
            'newness' => 0,
        ]);
        $form->isValid();

        $this->cartFacade->addItem(
            new CartItemAddDTO(
                cart: $cart,
                productId: $productId,
                productSaleElementId: $pseId,
                quantity: $quantity,
                append: true
            )
        );

        if ($this->pendingDelete && $this->pendingDelete['pseId'] === $pseId) {
            $this->pendingDelete = null;
        }
        $this->fetchCart();

        $this->emit(CheckoutEvents::ADD_ITEM_EVENT, [
            'pseId' => $pseId,
        ]);
    }
}
