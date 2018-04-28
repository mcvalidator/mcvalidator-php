<?php

namespace McValidator;

use McValidator\Data\SectionDefinition;
use McValidator\Support\ListOf;
use McValidator\Support\ShapeOf;
use McValidator\Support\Valid;

if (!function_exists('shape_of')) {
    /**
     * @param array $options
     * @param mixed ...$sections
     * @return Support\Builder|Support\ShapeOfBuilder
     * @throws \Exception
     */
    function shape_of(array $options, ...$sections)
    {
        return ShapeOf::build(dict($options), ...$sections);
    }
}

if (!function_exists('list_of')) {
    /**
     * @param mixed ...$sections
     * @return Support\Builder
     * @throws \Exception
     */
    function list_of(...$sections)
    {
        return ListOf::build($sections);
    }
}

if (!function_exists('valid')) {
    /**
     * @param mixed ...$sections
     * @return Support\Builder
     * @throws \Exception
     */
    function valid(...$sections)
    {
        return Valid::build($sections);
    }
}

if (!function_exists('section')) {
    function section($section, $options = null)
    {
        return new SectionDefinition($section, $options);
    }
}

if (!function_exists('with')) {
    /**
     * Return the given object. Useful for chaining.
     *
     * @param  mixed $object
     * @return mixed
     */
    function with($object)
    {
        return $object;
    }
}

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('lazydebug')) {
    function lazydebug(...$values)
    {
        $trace = debug_backtrace();

        array_shift($trace);

        if (count($trace)) {
            $callee = $trace[0];
            echo sprintf("%s => line: %s\n", @$callee['file'], @$callee['line']);
        }

        if (function_exists('dump')) {
            dump(...$values);
        } else {
            var_export(...$values);
        }

        exit;
    }
}


if (!function_exists('mc_error_handler')) {
    function mc_error_handler($errno, $errstr, $errfile, $errline)
    {
        if (E_RECOVERABLE_ERROR === $errno) {
            throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
        }

        return false;
    }

    set_error_handler('\McValidator\mc_error_handler');
}