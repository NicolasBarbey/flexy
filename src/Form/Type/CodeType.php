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

namespace FlexyBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class CodeType extends TextType
{
    public static function getExtendedTypes(): iterable
    {
        return [TextType::class];
    }

    public function getBlockPrefix(): string
    {
        return 'code';
    }
}
