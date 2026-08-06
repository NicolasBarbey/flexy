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

namespace FlexyBundle\Event\FilterField;

use Thelia\Core\Event\ActionEvent;

/**
 * Extension point letting a module swap the form type or options used to render a product filter
 * before it is added to the filter form. Not listened to by the theme itself.
 */
class FilterFieldRenderedEvent extends ActionEvent
{
    public function __construct(
        private string $name,
        private string $type,
        private array $options,
        private readonly array $filter = [],
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getFilter(): array
    {
        return $this->filter;
    }
}
