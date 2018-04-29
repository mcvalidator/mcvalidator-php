<?php


namespace McValidator\Sections\Rules;

use Heterogeny\Dict;
use Heterogeny\Seq;
use McValidator\Contracts\Pipeable;
use McValidator\Contracts\Section;
use McValidator\Data\Capsule;
use McValidator\Data\Error;
use McValidator\Data\Field;
use McValidator\Data\NonExistentValue;
use McValidator\Data\OptionsBag;
use McValidator\Data\State;
use McValidator\Data\Value;
use function McValidator\lazydebug;
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
    protected function receive(Capsule $capsule)
    {
        /** @var Dict $keys */
        $keys = $capsule->getOptions()->getValue();

        /** @var Pipeable $source */
        $source = $capsule->getSource();

        /** @var State $state */
        $state = $capsule->getState();

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
                new NonExistentValue($innerState)
            );

            $value = $pipe->pump($value, $innerState);

            $result = $result->set($key, $value);

            $innerState = $value->getState();
        }

        return $capsule
            ->newValue($result)
            ->setState($state->merge($innerState));
    }
}