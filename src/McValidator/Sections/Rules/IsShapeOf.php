<?php


namespace McValidator\Sections\Rules;

use Heterogeny\Dict;
use Heterogeny\Seq;
use McValidator\Contracts\Section;
use McValidator\Data\Capsule;
use McValidator\Data\ExplicitNonExistentValue;
use McValidator\Data\Field;
use McValidator\Data\NonExistentValue;
use McValidator\Data\OptionsBag;
use McValidator\Data\State;
use McValidator\Data\Value;
use McValidator\Pipe;

class IsShapeOf extends Section
{
    protected $required = true;
    /**
     * @param OptionsBag $options
     * @throws \Exception
     */
    public function validateOptions(OptionsBag $options)
    {
        $shape = $options->getValue();

        if (is_null($shape) ||
            (!$shape instanceof Dict)) {
            throw new \Exception("IsShapeOf `\$options` must be a `\Heterogeny\Dict`.");
        }
    }

    /**
     * @param Capsule $capsule
     * @param State $state
     * @return $this|Capsule
     * @throws \Exception
     */
    protected function receive(Capsule $capsule)
    {
        /** @var Dict $keys */
        $keys = $capsule->getOptions()->getValue();

        /** @var State $state */
        $state = $capsule->getState();

        /** @var Seq $values */
        $values = $capsule->getValue()->get(false, true, true);

        if ($values === null) {
            $values = dict();
        }

        if (!$values instanceof Dict) {
            $type = gettype($values);
            throw new \Exception("Value is not a `\Heterogeny\Dict`, received $type");
        }

        $parentField = $capsule->getField();

        $pipeMap = $keys->map(function ($key, $sections) use ($parentField) {
            if (!is_array($sections)) {
                $sections = [$sections];
            }

            $actualField = new Field($key, $parentField);

            $pipe = Pipe::build($actualField, null, $sections);

            return $pipe;
        });

        $innerState = new State();
        $result = \dict();

        /**
         * @var string $key
         * @var Pipe $pipe
         */
        foreach ($pipeMap as $key => $pipe) {
            /** @var Value $value */
            $value = $values->getOrElse(
                $key,
                new NonExistentValue($innerState, $capsule->getValue())
            );

            if ($value === null) {
                $value = new ExplicitNonExistentValue($innerState, $capsule->getValue());
            }

            if (!$value instanceof Value) {
                $value = new Value($value, null, $innerState, $capsule->getValue());
            }

            $newValue = $pipe->pump($value);

            $result = $result->set($key, $newValue);

            $innerState = $newValue->getState();
        }

        return $capsule
            ->newValue($result)
            ->setState($state->merge($innerState));
    }
}