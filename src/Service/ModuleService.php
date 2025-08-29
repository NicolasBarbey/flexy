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

namespace FlexyBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\Module;
use Thelia\Tools\URL;

class ModuleService
{
    public function __construct(protected readonly ContainerInterface $container, protected readonly Session $session)
    {
    }

    public function getModuleLogoUrl(Module $module, $region = 'full', $size = '%5E*!40,40'): string
    {
        $imageId = $module->getModuleImages()->getFirst()?->getId();

        return URL::getInstance()->absoluteUrl('legacy-image-library'.DS.'module_image_'.$imageId.DS.$region.DS.$size.DS.'0'.DS.'default.webp');
    }
}
