<?php

namespace McValidator\Support;

use Heterogeny\Dict;
use McValidator\Contracts\Pipeable;
use McValidator\Contracts\Section;
use McValidator\Data\Field;
use McValidator\Base;
use McValidator\Pipe;

class ShapeOf
{
    /**
     * @param Dict $options
     * @throws \Exception
     */
    private static function validate(Dict $options)
    {
        foreach ($options as $key => $value) {
            if (!is_string($key)) {
                throw new \Exception("`shape_of` `\$options` must be a key-value array with string keys");
            }

            Section::isValidOrFail($value);
        }
    }

    /**
     * @param array $options
     * @throws \Exception
     */
    private static function validateExtra(array $options)
    {
        foreach ($options as $value) {
            Section::isValidOrFail($value);
        }
    }

    /**
     * @param Dict $options
     * @param $sections
     * @return ShapeOfBuilder
     * @throws \Exception
     */
    public static function build(Dict $options, ...$sections): Builder
    {
        self::validate($options);
        self::validateExtra($sections);

        return new ShapeOfBuilder($options, $sections);
    }
}