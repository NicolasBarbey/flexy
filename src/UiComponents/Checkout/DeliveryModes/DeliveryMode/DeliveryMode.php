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

namespace FlexyBundle\UiComponents\Checkout\DeliveryModes\DeliveryMode;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: "Flexy:Checkout:DeliveryModes:DeliveryMode", template: '@UiComponents/Checkout/DeliveryModes/DeliveryMode/DeliveryMode.html.twig')]
class DeliveryMode
{

    public bool $checked = false;
    public int $moduleId;
    public string $optionCode;
    public string $title;
    public string $date;
    public string $price;
}
