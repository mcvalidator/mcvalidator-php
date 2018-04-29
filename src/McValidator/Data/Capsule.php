<?php


namespace McValidator\Data;


use McValidator\Contracts\Pipeable;
use McValidator\Contracts\Section;
use McValidator\Contracts\Splitter;

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
    public function __construct($value, $source, $field, OptionsBag $options)
    {
        $this->value = $value;
        $this->source = $source;
        $this->field = $field;
        $this->options = $options;
        $this->state = $value->getState();
    }

    /**
     * @param Value $value
     * @param State $state
     * @param Splitter $splitter
     * @return Capsule
     */
    public static function fromSectionWrapper(Value $value, Splitter $splitter)
    {
        return new self(
            $value,
            $splitter->getPipe(),
            $splitter->getField(),
            $splitter->getOptions()
        );
    }

    public function newValue($value, ?State $state = null)
    {
        if ($value instanceof Value) {
            $this->value = $value;
            $this->state = $value->getState();

            return $this;
        }

        $nextState = $this->state;

        if (!$value instanceof \Closure) {
            $this->value = new Value(
                $value,
                $this->value,
                $nextState
            );

            return $this;
        }

        $this->value = new Value(
            $value($this->value->get()),
            $this->value,
            $nextState
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
        $this->value = $this->value->setState($state);

        $this->state = $state;

        return $this;
    }

    public function addError($message, Section $section) {
        return $this->setState(
          $this->state->addError($this->getField(), $message, $section)
        );
    }
}