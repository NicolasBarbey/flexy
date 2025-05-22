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

namespace FlexyBundle\Twig\Organisms\Modules;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\Base\ModuleQuery;
use Thelia\Service\Model\AddressService;
use Thelia\Service\Model\CartService;
use TwigEngine\Service\DataAccess\DataAccessService;

#[AsLiveComponent(template: '@components/Organisms/Modules/HomeDelivery/HomeDeliveryModules.html.twig')]
class HomeDeliveryModules extends BaseFrontController
{
    use ComponentToolsTrait;
    use DefaultActionTrait;
    #[LiveProp()]
    public ?array $modules = [];

    #[LiveProp(writable: true)]
    public ?int $selectedDeliveryModuleId = null;

    public function __construct(
        private readonly Session $session,
        private readonly AddressService $addressService,
        private readonly CartService $cartService,
        private DataAccessService $dataAccessService,
    ) {
    }

    public function mount(): void
    {
        $deliveryModules = $this->dataAccessService->resources('/api/front/delivery_modules', ['by_code' => 1]);

        $this->modules = $deliveryModules['delivery'];

        $this->selectedDeliveryModuleId = $this->session->getOrder()->getDeliveryModuleId();
    }

    #[LiveAction]
    public function setSelectedDeliveryModuleId(?int $id): void
    {
        $this->selectedDeliveryModuleId = $id;
        /** @var \Thelia\Model\Module */
        $selectedModule = ModuleQuery::create()->filterById($this->selectedDeliveryModuleId)->findOne();
        $this->session->getOrder()->setModuleRelatedByDeliveryModuleId($selectedModule);
    }
}
