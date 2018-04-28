<?php


namespace McValidator\Data;


use McValidator\Contracts\Pipeable;
use McValidator\Contracts\Splitter;
use McValidator\Data\State;

final class Capsule
{
    /**
     * @var Pipeable
     */
    private $source;

    /**
     * @var Value
     */
    private $value;

    /**
     * @var Field
     */
    private $field;

    /**
     * @var OptionsBag
     */
    private $options;

    /**
     * @var State
     */
    private $state;

    /**
     * Capsule constructor.
     * @param $value Value
     * @param $source Pipeable
     * @param $field Field
     * @param OptionsBag $options
     * @param State $state
     */
    public function __construct($value, $source, $field, OptionsBag $options, State $state)
    {
        $this->value = $value;
        $this->source = $source;
        $this->field = $field;
        $this->options = $options;
        $this->state = $state;
    }

    /**
     * @param Value $value
     * @param Splitter $splitter
     * @return Capsule
     */
    public static function fromSectionWrapper(Value $value, Splitter $splitter)
    {
        return new self(
            $value,
            $splitter->getPipe(),
            $splitter->getField(),
            $splitter->getOptions(),
            $value->getState()
        );
    }

    public function newValue($value)
    {
        if (!$value instanceof \Closure) {
            $this->value = new Value(
                $value,
                $this->value,
                $this->state
            );

            return $this;
        }

        $this->value = new Value(
            $value($this->value->get()),
            $this->value,
            $this->state
        );

        return $this;
    }

    /**
     * @return Value
     */
    public function getValue()
    {
        return $this->value;
    }

    public function exists()
    {
        return !$this->value->get() instanceof NonExistentValue;
    }

    /**
     * @return Field
     */
    public function getField(): Field
    {
        return $this->field;
    }

    /**
     * @return OptionsBag
     */
    public function getOptions(): OptionsBag
    {
        return $this->options;
    }

    /**
     * @return Pipeable
     */
    public function getSource(): Pipeable
    {
        return $this->source;
    }

    /**
     * @return State
     */
    public function getState(): State
    {
        return $this->state;
    }

    /**
     * @param State $state
     * @return State
     */
    public function setState(State $state): Capsule
    {
        $this->state = $state;

        return $this;
    }
}