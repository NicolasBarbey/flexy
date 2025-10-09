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

namespace FlexyBundle\UiComponents\HeaderButton;

use FlexyBundle\UiComponents\Button\Button;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;


#[AsTwigComponent(name: 'Flexy:HeaderButton', template: '@UiComponents/HeaderButton/HeaderButton.html.twig')]
class HeaderButton extends Button
{
    public string $type = 'button';

    #[PostMount()]
    public function checkIfIsLink(array $data): array
    {
        if (isset($data['href'])) {
            $this->type = 'a';
        }

        return $data;
    }
}
