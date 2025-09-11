<?php

declare(strict_types=1);


namespace FlexyBundle\UiComponents\Checkout\Gateway;

use FlexyBundle\UiComponents\Checkout\CheckoutEvents;
use Propel\Runtime\Map\TableMap;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Api\Service\DataAccess\DataAccessService;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Domain\Cart\CartFacade;
use Thelia\Domain\Checkout\DTO\CheckoutDTO;
use Thelia\Model\Customer;
use TwigEngine\Service\DataAccess\AttributeAccessService;

#[AsLiveComponent(name: 'Flexy:Checkout:Gateway', template: '@UiComponents/Checkout/Gateway/Gateway.html.twig')]
class Gateway
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    public function __construct(
        private readonly DataAccessService $dataAccessService,
        private readonly AttributeAccessService $attributeAccessService,
        private readonly CartFacade $cartFacade,
        private readonly Session $session,
    ) {
    }
}
