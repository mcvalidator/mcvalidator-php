<?php


namespace McValidator\Sections\Rules;

use Heterogeny\Seq;
use McValidator\Contracts\Pipeable;
use McValidator\Contracts\Section;
use McValidator\Data\Capsule;
use McValidator\Data\Field;
use McValidator\Data\OptionsBag;
use McValidator\Data\SectionDefinition;
use McValidator\Data\State;
use McValidator\Pipe;
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
    protected function receive(Capsule $capsule)
    {
        /** @var array $options */
        $sections = $capsule->getOptions()->getValue();

        /** @var Seq $values */
        $values = $capsule->getValue()->get();

        if ($values === null) {
            $values = seq();
        }

        /** @var Field $parentField */
        $parentField = $capsule->getField();

        /** @var State $state */
        $state = $capsule->getState();

        if (!$values instanceof Seq) {
            $type = gettype($values);
            throw new \Exception("Value is not a `\Heterogeny\Seq`, received $type");
        }

        $field = new Field();

        $pipe = Pipe::build($field, null, $sections);

        $listState = new State();

        $result = seq();

        foreach ($values as $key => $value) {
            $actualField = new Field($key, $parentField);

            $value = $pipe->pump($value, new State());

            $listState = $listState->merge(
                $value
                    ->getState()
                    ->prefixWith($actualField)
            );

            $result = $result->append($value);
        }

        return $capsule
            ->newValue($result)
            ->setState($state->merge($listState));
    }
}