<?php


namespace McValidator\Sections\Rules;

use Heterogeny\Dict;
use Heterogeny\Seq;
use McValidator\Contracts\Pipeable;
use McValidator\Contracts\Section;
use McValidator\Data\Capsule;
use McValidator\Data\Field;
use McValidator\Data\NonExistentValue;
use McValidator\Data\OptionsBag;
use McValidator\Data\State;
use McValidator\Pipe;

class IsShapeOf extends Section
{
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
    protected function receive(Capsule $capsule, State $state)
    {
        /** @var Dict $keys */
        $keys = $capsule->getOptions()->getValue();

        /** @var Pipeable $source */
        $source = $capsule->getSource();

        /** @var Seq $values */
        $values = $capsule->getValue()->get();
        if ($values === null) {
            $values = dict();
        }

        if (!$values instanceof Dict) {
            $type = gettype($values);
            throw new \Exception("Value is not a `\Heterogeny\Dict`, received $type");
        }

        $parentField = $capsule->getField();

        $pipeMap = $keys->map(function ($key, $sections) use ($parentField, $source) {
            if (!is_array($sections)) {
                $sections = [$sections];
            }

            $actualField = new Field($key, $parentField);

            $pipe = Pipe::build($actualField, null, $sections);

            return $pipe;
        });

        $innerState = new State($source);

        $result = $pipeMap->map(function ($key, Pipe $pipe) use ($values, $innerState, $state) {
            $value = $values->getOrElse($key, new NonExistentValue());

            if ($value instanceof NonExistentValue) {
                return $pipe->pump(null, $innerState);
            }

            return $pipe->pump($value, $innerState);
        });

        $state->merge($innerState);

        return $capsule->newValue($result);
    }
}