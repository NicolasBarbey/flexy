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

use FlexyBundle\Event\FilterField\FilterCheckboxRendered;
use FlexyBundle\Event\FilterField\FilterRangeRendered;
use FlexyBundle\Event\FilterField\FilterTextRendered;
use FlexyBundle\Form\Type\FieldsetType;
use FlexyBundle\Form\Type\PillType;
use FlexyBundle\Form\Type\RangeFilterType;
use FlexyBundle\Form\Type\RangeGroupType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FormService
{
    public const RANGE_SUB_INPUTS = ['min', 'max'];

    public function __construct(protected readonly EventDispatcherInterface $dispatcher) {}

    public function renderFieldFromFieldType(array $filter, $fieldset, array $tfilters): void
    {
        if (!$fieldset->has($filter['type'])) {
            $fieldset->add($fieldset->create(
                $filter['type'],
                FieldsetType::class,
                [
                    'by_reference' => true,
                    'inherit_data' => false,
                ]
            ));
        }
        match ($filter['fieldType'] ?? 'checkbox') {
            'checkbox', 'radio' => $this->renderCheckbox($filter, $fieldset->get($filter['type']), $tfilters),
            'input' => $this->renderInput($filter, $fieldset),
            'range' => $this->renderRange($filter, $fieldset->get($filter['type']), $tfilters),
            'delta' => $this->renderDelta($filter, $fieldset->get($filter['type']), $tfilters),
        };
    }

    private function renderCheckbox(array $filter, $fieldset, array $tfilters): void
    {
        $values = [];

        foreach ($filter['values'] as $value) {
            $values[$value['title'] ?? ''] = $value['id'];
        }


        $formEvent = new FilterCheckboxRendered(
            \sprintf('%d', $filter['id']),
            PillType::class,
            [
                'label' => $filter['title'],
                'choices' => $values,
                'data' => $tfilters[$filter['type']][$filter['id']] ?? [],
                'multiple' => true,
                'required' => false,
            ],
            $filter);
        $this->dispatcher->dispatch($formEvent);

        $fieldset->add(
            $formEvent->getName(),
            $formEvent->getType(),
            $formEvent->getOptions()
        );
    }

    private function renderInput(array $filter, $fieldset): void
    {
        $formEvent = new FilterTextRendered('sf', TextType::class, [
            'label' => $filter['title'], $filter
        ]);
        $this->dispatcher->dispatch($formEvent);

        $fieldset->add(
            $formEvent->getName(),
            $formEvent->getType(),
            $formEvent->getOptions()
        );
    }

    private function renderDelta(array $filter, $fieldset, $tfilters): void
    {
        $groupName = \sprintf('%d', $filter['id']);

        $fieldset->add($fieldset->create(
            $groupName,
            RangeGroupType::class,
            [
                'label' => $filter['title'],
                'mapped' => true,
            ]
        ));

        $min = min(array_column($filter['values'], 'title'));
        $max = max(array_column($filter['values'], 'title'));

        $currentMin = $tfilters[$filter['type']][$filter['id']]['min'] ?? $min;
        $currentMax = $tfilters[$filter['type']][$filter['id']]['max'] ?? $max;

        foreach (self::RANGE_SUB_INPUTS as $range) {
            $fieldset->get($groupName)->add(
                $range,
                RangeType::class,
                [
                    'attr' => [
                        'min' => $min,
                        'max' => $max,
                        'data-delta-target' => $range,
                        'data-action' => 'delta#updateInput',
                    ],
                    'data' => $range === 'min' ? $currentMin : $currentMax,
                    'label' => $range,
                ]
            );
        }
    }

    private function renderRange(array $filter, $fieldset, $tfilters): void
    {
        $min = min(array_column($filter['values'], 'title'));
        $max = max(array_column($filter['values'], 'title'));
        $formEvent = new FilterRangeRendered( \sprintf('%d', $filter['id']),
            RangeFilterType::class,
            [
                'label' => $filter['title'],
                'attr' => [
                    'min' => $min,
                    'max' => $max,
                    'step' => 10,
                ],
                'data' => $tfilters[$filter['type']][$filter['id']] ?? null,
            ],
            $filter);
        $this->dispatcher->dispatch($formEvent);

        $fieldset->add(
            $formEvent->getName(),
            $formEvent->getType(),
            $formEvent->getOptions()
        );
    }
}
