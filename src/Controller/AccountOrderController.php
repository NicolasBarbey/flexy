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

namespace FlexyBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Api\Service\DataAccess\DataAccessService;
use Thelia\Model\ConfigQuery;
use Thelia\Model\Order;
use Thelia\Model\OrderQuery;

#[Route('/account', name: 'account_')]
class AccountOrderController extends FlexyController
{
    #[Route('/orders', name: 'orders')]
    public function orders(): Response
    {
        $this->checkAuth();

        return $this->render('account-orders');
    }

    #[Route('/order/{orderId}', name: 'order', requirements: ['orderId' => '\d+'])]
    public function order(DataAccessService $dataAccessService, int $orderId): Response
    {
        $order = $this->findCustomerOrder($orderId);

        // The template reads the order through the API: if that comes back empty it would
        // render an empty shell in 200. Memoized, so this costs nothing extra.
        if (null === $dataAccessService->resources('/api/front/account/orders/'.$orderId)) {
            throw new NotFoundHttpException();
        }

        return $this->render('account-order', ['orderId' => $orderId]);
    }

    #[Route('/order/pdf/delivery/{orderId}', name: 'order_pdf_delivery', requirements: ['orderId' => '\d+'])]
    public function generateDeliveryPdf(EventDispatcherInterface $eventDispatcher, int $orderId): Response
    {
        $this->findCustomerOrder($orderId);

        return $this->generateOrderPdf(
            $eventDispatcher,
            $orderId,
            ConfigQuery::read('pdf_delivery_file', 'delivery'),
            checkOrderStatus: true,
            checkAdminUser: true,
        );
    }

    #[Route('/order/pdf/invoice/{orderId}', name: 'order_pdf_invoice', requirements: ['orderId' => '\d+'])]
    public function generateInvoicePdf(EventDispatcherInterface $eventDispatcher, int $orderId): Response
    {
        $this->findCustomerOrder($orderId);

        return $this->generateOrderPdf(
            $eventDispatcher,
            $orderId,
            ConfigQuery::read('pdf_invoice_file', 'invoice'),
            checkOrderStatus: true,
            checkAdminUser: true,
        );
    }

    #[Route('/order/pdf/quotation/{orderId}', name: 'order_pdf_quotation', requirements: ['orderId' => '\d+'])]
    public function generateQuotationPdf(EventDispatcherInterface $eventDispatcher, int $orderId): Response
    {
        $this->findCustomerOrder($orderId);

        return $this->generateOrderPdf(
            $eventDispatcher,
            $orderId,
            // A quotation is by definition not paid: the status guard must stay off here.
            'quotation',
            checkOrderStatus: false,
            checkAdminUser: false,
        );
    }

    /**
     * Orders are only ever reached through their owner: never look one up by primary key
     * alone, or a logged-in customer reads someone else's order by walking the sequential
     * ids. An unknown id and another customer's id answer the same 404, so neither response
     * confirms that the other order exists.
     */
    private function findCustomerOrder(int $orderId): Order
    {
        $this->checkAuth();

        $customerId = $this->getSecurityContext()->getCustomerUser()?->getId();
        $order = null === $customerId
            ? null
            : OrderQuery::create()->filterByCustomerId($customerId)->findPk($orderId);

        if (null === $order) {
            throw new NotFoundHttpException();
        }

        return $order;
    }
}
