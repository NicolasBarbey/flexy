<?php

declare(strict_types=1);

namespace FlexyBundle\UiComponents\RadioButton;

use FlexyBundle\UiComponents\Button\Button;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'Flexy:RadioButton', template: '@UiComponents/RadioButton/RadioButton.html.twig')]
class RadioButton extends Button
{
}
