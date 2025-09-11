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

namespace FlexyBundle\UiComponents\Button;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent(name: 'Flexy:Button', template: '@UiComponents/Button/Button.html.twig')]
class Button
{
    public string $tag = 'button';
    public string $text;

    #[PostMount()]
    public function checkIfIsLink(array $data): array
    {
        if ($data['href'] ?? false) {
            $this->tag = 'a';
        }

        return $data;
    }
}
