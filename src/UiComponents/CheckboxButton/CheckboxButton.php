<?php

declare(strict_types=1);

namespace FlexyBundle\UiComponents\CheckboxButton;

use FlexyBundle\UiComponents\Button\Button;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'Flexy:CheckboxButton', template: '@UiComponents/CheckboxButton/CheckboxButton.html.twig')]
class CheckboxButton extends Button
{
}
