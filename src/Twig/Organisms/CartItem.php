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

use Propel\Runtime\Exception\PropelException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\AttributeCombination;
use Thelia\Model\CartItem as CartItemModel;
use Thelia\Model\CartItemQuery;
use Thelia\Model\ProductImage;
use Thelia\Model\ProductSaleElementsProductImage;
use TwigEngine\Service\DataAccess\DataAccessService;

#[AsLiveComponent(template: '@components/Organisms/CartItem/CartItem.html.twig')]
class CartItem
{
    use DefaultActionTrait;

    public ?string $cartItemId = '';

    #[ExposeInTemplate]
    public ?array $cartItem = null;

    protected ?string $locale = null;

    protected ?CartItemModel $cartItemModel = null;

    #[ExposeInTemplate]
    public ?int $imageId = null;

    #[ExposeInTemplate]
    public ?array $attributesAv = null;

    public function __construct(
        private DataAccessService $dataAccessService,
        private RequestStack $requestStack
    ) {
    }

    public function mount(string $cartItemId): void
    {
        $request = $this->requestStack->getCurrentRequest();

        /** @var Session $session */
        $session = $request->getSession();

        $this->locale = $session->getLang()->getLocale();
        $this->cartItemId = $cartItemId;
        $this->cartItemModel = CartItemQuery::create()->findPk($cartItemId);
        $this->setCartItem($cartItemId);
        $this->setImage();
    }

    public function getCartItem()
    {
        return $this->cartItem;
    }

    public function getImageId()
    {
        return $this->imageId;
    }

    public function getAttributesAv()
    {
        if ($this->attributesAv !== null) {
            return $this->attributesAv;
        }

        $combinations = $this->cartItemModel->getProductSaleElements()->getAttributeCombinations();
        $attributesAv = [];
        /** @var AttributeCombination $combination */
        foreach ($combinations as $combination) {
            $title = $combination->getAttribute()->setLocale($this->locale)->getTitle();
            $av = $combination->getAttributeAv()->setLocale($this->locale)->getTitle();

            $attributesAv[$title] = $av;
        }

        $this->attributesAv = $attributesAv;

        return $this->attributesAv;
    }

    public function setCartItem($cartItemId)
    {
        if (null !== $this->cartItem) {
            return;
        }

        if ('' === $this->cartItemId) {
            return null;
        }
        $this->cartItem = $this->dataAccessService->resources('/api/front/cart_items/'.$this->cartItemId);
    }

    /**
     * @throws PropelException
     */
    private function setImage(): void
    {
        /** @var ProductSaleElementsProductImage $pseImage */
        $pseImage = $this->cartItemModel->getProductSaleElements()->getProductSaleElementsProductImages()->getFirst();

        if ($pseImage) {
            $this->imageId = $pseImage->getProductImageId();

            return;
        }
        /** @var ProductImage $productImage */
        $productImage = $this->cartItemModel->getProduct()->getProductImages()->getFirst();

        if ($productImage) {
            $this->imageId = $productImage->getId();
        }
    }

    //  #[LiveAction]
    //  public function saveProduct()
    //  {
    //    // ...
    //
    //    $this->emit('productAdded');
    //  }
}
