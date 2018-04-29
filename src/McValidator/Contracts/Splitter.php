<?php


namespace McValidator\Contracts;


use McValidator\Data\Capsule;
use McValidator\Data\Field;
use McValidator\Data\OptionsBag;
use McValidator\Data\Value;
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

    /**
     * @param Value $value
     * @return Value
     * @throws \Exception
     */
    function receive(Value $value): Value
    {
        if ($this->wrapped instanceof Section) {
            $capsule = Capsule::fromSectionWrapper($value, $this);

            try {
                $result = $this
                    ->wrapped
                    ->evaluate($capsule);

                return $result->getValue();
            } catch (\Exception $ex) {
                $capsule = $capsule->addError(
                    $ex->getMessage(),
                    $this->wrapped
                );

                return $capsule->getValue()->invalid();
            }
        } elseif ($this->wrapped instanceof Pipeable) {
            return $this->wrapped->pump($value);
        }

        throw new \Exception("Unexpected result on `Splitter`");
    }
}