<?php


namespace FlexyBundle\UiComponents\PickupPoint;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'Flexy:PickupPoint', template: '@UiComponents/PickupPoint/PickupPoint.html.twig')]
class PickupPoint
{
    use ComponentToolsTrait;
    use DefaultActionTrait;
}
