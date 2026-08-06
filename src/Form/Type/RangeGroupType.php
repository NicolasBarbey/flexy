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

use Symfony\Component\Form\Extension\Core\Type\FormType;

/**
 * Groups the min/max sliders of a delta filter, rendered by the `range_group_row` form theme block.
 */
final class RangeGroupType extends FormType
{
    public function getBlockPrefix(): string
    {
        return 'range_group';
    }
}
