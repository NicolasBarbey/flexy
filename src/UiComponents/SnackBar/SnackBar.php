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

namespace FlexyBundle\UiComponents\SnackBar;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;


#[AsTwigComponent(name: 'Flexy:SnackBar', template: '@UiComponents/SnackBar/SnackBar.html.twig')]
class SnackBar
{
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const INFO = 'info';
    public const VALIDATED = 'validated';
    public const LIGHT = 'light';
    public const DARK = 'dark';
}
