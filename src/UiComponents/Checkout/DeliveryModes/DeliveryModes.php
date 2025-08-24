<?php

namespace FlexyBundle\UiComponents\Checkout\DeliveryModes;

use FlexyBundle\UiComponents\Checkout\CartManipulationTrait;
use FlexyBundle\UiComponents\Checkout\CheckoutEvents;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Service\Model\DeliveryService;

#[AsLiveComponent(name: "Flexy:Checkout:DeliveryModes", template: '@UiComponents/Checkout/DeliveryModes/DeliveryModes.html.twig')]
class DeliveryModes
{

    use ComponentToolsTrait;
    use DefaultActionTrait;
    use CartManipulationTrait;

    #[LiveProp(writable: true)]
    public ?string $selectedModule = null;

    public function __construct(private readonly DeliveryService $deliveryModuleService,) {}

    public function getDeliveryModulesOptions()
    {
        $deliveryModules = $this->deliveryModuleService->getValidDeliveryModuleCollection()->getAll();

        $deliveryOptions = [];

        foreach ($deliveryModules as $module) {
            if (!isset($module['valid']) || !$module['valid']) {
                continue;
            }

            foreach ($module['options'] as $option) {


                $deliveryOptions[$option['code']] = [
                    'title' => isset($module['title']) ? $module['title'] : $module['code'],
                    'moduleId' => $module['id'],
                    'deliveryMode' => $module['deliveryMode'],
                    ...$option
                ];
            }
        }

        return $deliveryOptions;
    }


    #[LiveListener(CheckoutEvents::SET_DELIVERY_MODULE_OPTION)]
    public function selectDeliveryModuleOption(#[LiveArg] string $optionCode, #[LiveArg] int $moduleId)
    {
        $this->cartService->setDeliveryModule($moduleId);
        $this->selectedModule = $moduleId;
    }
}
