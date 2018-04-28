<?php


namespace McValidator\Sections\Rules;

use Heterogeny\Seq;
use McValidator\Contracts\Pipeable;
use McValidator\Contracts\Section;
use McValidator\Data\Capsule;
use McValidator\Data\Field;
use McValidator\Data\OptionsBag;
use McValidator\Data\SectionDefinition;
use McValidator\Pipe;
use McValidator\Data\State;
use McValidator\Support\Builder;

class IsListOf extends Section
{
    /**
     * @param OptionsBag $options
     * @throws \Exception
     */
    public function validateOptions(OptionsBag $options)
    {
        $values = $options->getValue();

        foreach ($values as $value) {
            if (!is_null($value) &&
                !($value instanceof Builder) &&
                !($value instanceof Section) &&
                !($value instanceof SectionDefinition) &&
                !is_string($value)) {
                $type = gettype($value);
                throw new \Exception("`IsListOf` `\$options` must be any of `Builder`, `Section`, `SectionDefinition` or `string`, `$type` found.");
            }
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
        /** @var array $options */
        $sections = $capsule->getOptions()->getValue();

        /** @var Pipeable $source */
        $source = $capsule->getSource();

        /** @var Seq $values */
        $values = $capsule->getValue()->get();
        if ($values === null) {
            $values = seq();
        }

        /** @var Field $parentField */
        $parentField = $capsule->getField();

        if (!$values instanceof Seq) {
            $type = gettype($values);
            throw new \Exception("Value is not a `\Heterogeny\Seq`, received $type");
        }

        $field = new Field();

        $pipe = Pipe::build($field, null, $sections);

        $listState = new State($source);

        $result = $values->mapWithIndex(function ($key, $value) use ($parentField, $pipe, $source, $listState, $state) {
            $actualField = new Field($key, $parentField);

            $innerState = new State($source);

            $value = $pipe->pump($value, $innerState);

            $innerState->prefixWith($actualField);
            $listState->merge($innerState);

            return $value;
        });

        $state->merge($listState);

        return $capsule->newValue($result);
    }
}