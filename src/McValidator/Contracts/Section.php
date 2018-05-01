<?php


namespace McValidator\Contracts;

use McValidator\Data\Capsule;
use McValidator\Data\InvalidValue;
use McValidator\Data\NonExistentValue;
use McValidator\Data\OptionsBag;
use McValidator\Data\SectionDefinition;
use McValidator\Data\State;
use McValidator\Support\Builder;

abstract class Section
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var boolean
     */
    protected $required;

    /**
     * @var Builder
     */
    protected $validation = null;

    public function __construct($identifier)
    {
        $this->identifier = $identifier;

        $this->setup();
    }

    protected function setup() { }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return Section
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function validateOptions(OptionsBag $options)
    {
    }

    /**
     * @param Capsule $capsule
     * @param State $state
     * @return Capsule
     */
    abstract protected function receive(Capsule $capsule);

    /**
     * @param Capsule $capsule
     * @param State $state
     * @return Capsule
     */
    public function evaluate(Capsule $capsule)
    {
        $value = $capsule->getValue();

        if ($value->exists() && !$this->required) {
            return $capsule;
        }

        if ($this->validation) {
            $validator = $this->validation->build(
                $capsule->getField()
            );

            $newValue = $validator->pump(
                $value,
                $capsule->getState()
            );

            return $capsule
                ->newValue($newValue);
        }

        return $this->receive($capsule);
    }

    /**
     * @param $section
     * @throws \Exception
     */
    public static function isValidOrFail($section)
    {
        if (!is_null($section) &&
            !($section instanceof Builder) &&
            !($section instanceof Section) &&
            !($section instanceof SectionDefinition) &&
            !is_string($section)
        ) {
            $type = gettype($section);

            throw new \Exception("Section must be any of `Builder`, `Section`, `SectionDefinition` or `string`, `$type` found.");
        }
    }
}