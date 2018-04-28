<?php


namespace McValidator\Contracts;

use McValidator\Data\Capsule;
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

    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

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
    abstract protected function receive(Capsule $capsule, State $state);

    /**
     * @param Capsule $capsule
     * @param State $state
     * @return Capsule
     */
    public function evaluate(Capsule $capsule, State $state)
    {
        return $this->receive($capsule, $state);
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