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

use FlexyBundle\Service\ProductSaleElementsService;
use FlexyBundle\UiComponents\Checkout\CartManipulationTrait;
use FlexyBundle\UiComponents\Checkout\CheckoutEvents;
use Propel\Runtime\Map\TableMap;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Form\Definition\FrontForm;
use Thelia\Service\Model\CartService;
use TwigEngine\Service\FormService;

#[AsLiveComponent(name: 'Flexy:Checkout:Cart', template: '@UiComponents/Checkout/Cart/Cart.html.twig')]
class Cart
{
    use CartManipulationTrait;
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp()]
    public ?array $pendingDelete = null;

    public function __construct(private ProductSaleElementsService $pseService, private FormService $formService) {}

    #[LiveAction]
    public function addCartItem(#[LiveArg] int $pseId, #[LiveArg] int $productId, #[LiveArg] ?int $quantity): void
    {
        if (!$pseId || !$productId) {
            return;
        }

        $form = $this->formService->getFormByName(FrontForm::CART_ADD);

        $form->submit([
            'product' => $productId,
            'product_sale_elements_id' => $pseId,
            'quantity' => $quantity ?? 1,
            'append' => 1,
            'newness' => 0,
        ]);
        $form->isValid();

        $this->cartService->addItem($form);

        if ($this->pendingDelete && $this->pendingDelete['pseId'] === $pseId) {
            $this->pendingDelete = null;
        }

        $this->emit(CheckoutEvents::ADD_ITEM_EVENT, [
            'pseId' => $pseId,
        ]);
    }


    #[LiveListener(CheckoutEvents::DELETE_ITEM_EVENT)]
    public function appendDeleted(#[LiveArg()] int $id): void
    {
        $sessionCart = $this->cartService->getCart();
        $items = $sessionCart->getCartItems();

        if (null === $items) return;

        foreach ($items as $item) {
            if ($item->getId() === $id) {

                $this->pendingDelete = [
                    'title' => $item->getProduct()->getTitle(),
                    'productId' => $item->getProduct()->getId(),
                    'pseId' => $item->getProductSaleElementsId(),
                    'attributesAv' => $this->pseService->getAttributesAvFromPse($item->getProductSaleElements()),
                    'quantity' => $item->getQuantity(),
                    'image' => null
                ];

                $pseImage = $item->getProductSaleElements()->getProductSaleElementsProductImages()->getFirst();

                if ($pseImage) {
                    $this->pendingDelete['image'] = $pseImage->getProductImageId();
                    break;
                }
                /** @var ProductImage $productImage */
                $productImage = $item->getProduct()->getProductImages()->getFirst();

                if ($productImage) {
                    $this->pendingDelete['image'] = $productImage->getId();
                }

                break;
            }
        }

        $this->cartService->deleteItem($id);
    }

    #[LiveAction]
    public function setQuantity(#[LiveArg] int $id, #[LiveArg] ?int $quantity = 1): void
    {
        if ($quantity < 2) {
            $quantity = 1;
        }
        $this->cartService->changeItem($id, $quantity);


        $this->emit(CheckoutEvents::UPDATE_ITEM_QUANTITY_EVENT, [
            'id' => $id,
            'quantity' => $quantity
        ]);
    }

    #[LiveAction]
    public function deleteCartItem(#[LiveArg] int $id): void
    {
        if (!$id) {
            return;
        }
        $this->emit(CheckoutEvents::DELETE_ITEM_EVENT, [
            'id' => $id,
        ]);
    }
}
