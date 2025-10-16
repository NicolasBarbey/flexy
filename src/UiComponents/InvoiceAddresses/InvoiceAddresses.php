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

namespace FlexyBundle\UiComponents\InvoiceAddresses;

use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Domain\Addressing\Exception\AddressNotFoundException;
use Thelia\Domain\Addressing\Service\AddressService;
use Thelia\Domain\Cart\CartFacade;
use Thelia\Domain\Checkout\DTO\CheckoutDTO;
use Thelia\Log\Tlog;
use Thelia\Model\Customer;

#[AsLiveComponent(name: 'Flexy:InvoiceAddresses', template: '@UiComponents/InvoiceAddresses/InvoiceAddresses.html.twig')]
class InvoiceAddresses extends BaseFrontController
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public array $addresses = [];

    #[LiveProp(writable: true, onUpdated: 'setInvoiceOrderAddressId')]
    public ?int $invoiceAddressId = null;

    #[LiveProp]
    public ?int $update = null;
    #[LiveProp]
    public bool $create = false;
    #[LiveProp]
    public bool $switchView = false;

    public function __construct(
        private readonly Session $session,
        private readonly AddressService $addressService,
        private readonly CartFacade $cartFacade,
    ) {
    }

    public function mount(): void
    {
        $cart = $this->cartFacade->getOrCreateFromSession();
        /** @var Customer $user */
        $user = $this->session->getCustomerUser();
        $addresses = $user->getAddresses()?->toArray(null, false, TableMap::TYPE_CAMELNAME);

        $this->addresses = $addresses;
        $this->invoiceAddressId = $cart->getAddressInvoiceId();
        $this->switchView = !$this->invoiceAddressId;
    }

    #[LiveListener('InvoiceAddresses:refresh')]
    public function refresh(): void
    {
        $this->mount();
        $this->create = false;
        $this->update = null;
        $this->switchView = false;
    }

    #[LiveAction]
    public function newAddress(): void
    {
        $this->create = true;
    }

    #[LiveAction]
    public function switchAddress(): void
    {
        $this->switchView = !$this->switchView;
    }

    #[LiveAction]
    public function setInvoiceOrderAddressId(): void
    {
        $this->cartFacade->setInvoiceAddress(new CheckoutDTO(
            cart: $this->cartFacade->getOrCreateFromSession(),
            invoiceAddressId: $this->invoiceAddressId
        ));
        $this->emit('resetCart');
    }

    #[LiveListener('cancelUpdateCreate')]
    public function cancelUpdateCreate(): void
    {
        $this->create = false;
        $this->update = null;
    }

    #[LiveListener('editAddress')]
    public function editAddress(#[LiveArg] int $id): void
    {
        $this->update = $id;
    }

    #[LiveListener('deleteAddress')]
    public function deleteAddress(#[LiveArg] int $id): void
    {
        $this->checkAuth();
        try {
            $this->addressService->deleteAddress($id);
            $this->refresh();
        } catch (AddressNotFoundException $e) {
            $this->addFlash('error', \sprintf('Error during address deletion : %s', $e->getMessage()));
        }
    }
}
