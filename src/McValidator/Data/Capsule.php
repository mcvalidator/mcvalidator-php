<?php


namespace McValidator\Data;


use McValidator\Contracts\Pipeable;
use McValidator\Contracts\Section;
use McValidator\Contracts\Splitter;

final class Capsule
{
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
     * @param $field Field
     * @param OptionsBag $options
     */
    public function __construct($value, $field, OptionsBag $options)
    {
        $this->value = $value;
        $this->field = $field;
        $this->options = $options;
        $this->state = $value->getState();
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
                $nextState,
                $this->value->getParent()
            );

            return $this;
        }

        $this->value = new Value(
            $value($this->value->get()),
            $this->value,
            $nextState,
            $this->value->getParent()
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
        return !$this->value instanceof NonExistentValue;
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