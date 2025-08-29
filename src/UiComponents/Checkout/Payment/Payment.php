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

namespace FlexyBundle\UiComponents\Checkout\Payment;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Api\Service\DataAccess\DataAccessService;

#[AsLiveComponent(name: 'Flexy:Checkout:Payment', template: '@UiComponents/Checkout/Payment/Payment.html.twig')]
class Payment
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?int $paymentModuleId = null;

    public function __construct(private readonly DataAccessService $dataAccessService)
    {
    }

    public function mount(): void
    {
    }

    public function getModules()
    {
        $modules = $this->dataAccessService->resources('/api/front/payment/modules');

        return $modules;
    }
}
