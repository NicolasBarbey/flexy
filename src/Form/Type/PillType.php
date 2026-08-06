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

namespace FlexyBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Choice field rendered as a list of FilterPill, by the `pill_widget` form theme block.
 */
final class PillType extends ChoiceType
{
    public function getBlockPrefix(): string
    {
        return 'pill';
    }
}
