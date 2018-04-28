<?php


namespace McValidator\Contracts;


use McValidator\Data\Capsule;
use McValidator\Data\Field;
use McValidator\Data\OptionsBag;
use McValidator\Data\Value;
use McValidator\Data\State;
use McValidator\Support\PumpIt;

/**
 * Wraps Section's execution.
 * @package McValidator\Contracts
 */
final class Splitter implements Pumps
{
    use PumpIt;

    /**
     * @var Field
     */
    private $field;

    /**
     * @var Pipeable
     */
    private $pipe;

    /**
     * @var Section
     */
    private $wrapped;

    /**
     * @var OptionsBag
     */
    private $options;

    /**
     * SectionWrapper constructor.
     * @param $field
     * @param $pipe Pipeable
     * @param $wrapped Section|Pipeable
     * @param $options
     */
    public function __construct(Field $field, $pipe, $wrapped, $options)
    {
        if (!$options instanceof OptionsBag) {
            $options = new OptionsBag($options);
        }

        if ($wrapped instanceof Section) {
            $wrapped->validateOptions($options);
        }

        $this->field = $field;
        $this->pipe = $pipe;

        $this->wrapped = $wrapped;
        $this->options = $options;
    }

    public function getField()
    {
        return $this->field;
    }

    public function getWrapped()
    {
        return $this->wrapped;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getPipe()
    {
        return $this->pipe;
    }

    function receive(Value $value, State $state): Value
    {
        if ($this->wrapped instanceof Section) {
            $capsule = Capsule::fromSectionWrapper($value, $this);

            try {
                return $this->wrapped->evaluate($capsule, $state)->getValue();
            } catch (\Exception $ex) {
                $state->addError($capsule->getField(), $ex->getMessage(), $this->wrapped);

                return $value->invalid();
            }
        } elseif ($this->wrapped instanceof Pipeable) {
            return $this->wrapped->pump($value, $state);
        }

        throw new \Exception("Unexpected result on `Splitter`");
    }
}