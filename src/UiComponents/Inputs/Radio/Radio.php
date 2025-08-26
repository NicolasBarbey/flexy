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

namespace FlexyBundle\UiComponents\Inputs\Radio;


use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;


#[AsTwigComponent(name: "Flexy:Inputs:Radio", template: '@UiComponents/Inputs/Radio/Radio.html.twig')]
class Radio
{
    public string $type = "div";
    public bool $hasError = false;
    public bool $disabled = false;
    public string $id;
    public string $name;
    public string $value = '';
    public bool $checked = false;
}
