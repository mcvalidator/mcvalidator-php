<?php

namespace McValidator\Data;

use McValidator\Contracts\Section;
use McValidator\Base;

class SectionDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $options;

    public function __construct(string $name, $options = null)
    {
        $this->name = $name;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return SectionDefinition
     */
    public function setName(string $name): SectionDefinition
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $options
     * @return SectionDefinition
     */
    public function setOptions($options): SectionDefinition
    {
        $this->options = $options;

        return $this;
    }

    public function getSection(): Section
    {
        return Base::getSection($this->name);
    }

}