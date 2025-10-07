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

namespace FlexyBundle\UiComponents\HeaderProfile;

use FlexyBundle\Controller\FlexyController;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;


#[AsTwigComponent(name: 'Flexy:HeaderProfile', template: '@UiComponents/HeaderProfile/HeaderProfile.html.twig')]
class HeaderProfile extends FlexyController
{
}
