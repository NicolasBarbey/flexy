<?php

namespace FlexyBundle\Event\FilterField;

use Thelia\Core\Event\ActionEvent;

class FilterFieldRenderedEvent  extends ActionEvent
{
    protected string $name;
    protected string $type;
    protected array $options;
    protected array $filter;

    public function __construct(string $name, string $type, array $options, array $filter = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
        $this->filter = $filter;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): FilterFieldRenderedEvent
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): FilterFieldRenderedEvent
    {
        $this->type = $type;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): FilterFieldRenderedEvent
    {
        $this->options = $options;
        return $this;
    }
    public function getFilter(): array
    {
        return $this->filter;
    }
    public function setFilter(array $filter): FilterFieldRenderedEvent
    {
        $this->filter = $filter;
        return $this;
    }
}
