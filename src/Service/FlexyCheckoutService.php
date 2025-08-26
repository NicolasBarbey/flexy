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

namespace FlexyBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Cart\CartCheckoutEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Domain\Cart\CartService;

class FlexyCheckoutService
{
    public function __construct(
        private CartService $cartService,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger
    ) {}

    public function setDeliveryModule(?int $deliveryModuleId = null): void
    {
        try {
            $cartCheckoutEvent = new CartCheckoutEvent($this->cartService->getCart());
            $cartCheckoutEvent->setDeliveryModuleId($deliveryModuleId);
            $this->eventDispatcher->dispatch($cartCheckoutEvent, TheliaEvents::CART_SET_DELIVERY_MODULE);

            // NEED_REVIEW
            $cartCheckoutEvent->getCart()->setDeliveryModuleId($cartCheckoutEvent->getDeliveryModuleId())->save();
        } catch (\Exception $e) {
            $this->logger->error(\sprintf('Failed to set delivery module : %s', $e->getMessage()));
            throw new \RuntimeException('Failed to set delivery module');
        }

        $this->handlePostageOnCart();
    }

    public function getDeliveryModule(): ?int
    {
        return $this->cartService->getCart()->getDeliveryModuleId();
    }

    public function setDeliveryAddress(?int $deliveryAddressId = null): void
    {
        try {
            $cartCheckoutEvent = new CartCheckoutEvent($this->cartService->getCart());
            $cartCheckoutEvent->setDeliveryAddressId($deliveryAddressId);
            $cartCheckoutEvent->setDeliveryModuleId(null); //NEED_REVIEW
            $this->eventDispatcher->dispatch($cartCheckoutEvent, TheliaEvents::CART_SET_DELIVERY_ADDRESS);

            // NEED_REVIEW
            $cartCheckoutEvent->getCart()->setAddressDeliveryId($deliveryAddressId)->save();

            $this->handlePostageOnCart();
        } catch (\Exception $e) {
            $this->logger->error(\sprintf('Failed to set delivery address : %s', $e->getMessage()));
            throw new \RuntimeException('Failed to set delivery address');
        }
    }

    public function getDeliveryAddress(): ?int
    {
        return $this->cartService->getCart()->getAddressDeliveryId();
    }

    public function handlePostageOnCart(): void
    {
        try {
            $postageEvent = new CartCheckoutEvent($this->cartService->getCart());
            $this->eventDispatcher->dispatch($postageEvent, TheliaEvents::CART_SET_POSTAGE);

            // NEED_REVIEW
            $postageEvent->getCart()->setPostage($postageEvent->getPostage())->save();
        } catch (\Exception $e) {
            $this->cartService->getCart()
                ->setPostage(null)
                ->setPostageTax(null)
                ->setPostageTaxRuleTitle(null)
                ->save();

            $this->logger->error(\sprintf('Failed to set postage : %s', $e->getMessage()));
            throw new \RuntimeException('Failed to set postage');
        }
    }
}
