<?php


namespace McValidator\Data;

/**
 * Representation of a explicit non existent value(a value that was sent null),
 * so null values will not be confused with "undefined"
 *
 * @package McValidator\Data
 */
class ExplicitNonExistentValue extends Value
{
    public function __construct(?State $state = null, ?Value $parent = null)
    {
        parent::__construct(null, null, $state, $parent);
    }

    public function exists()
    {
        return false;
    }
}