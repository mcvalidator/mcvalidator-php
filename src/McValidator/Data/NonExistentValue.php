<?php


namespace McValidator\Data;

/**
 * Representation of a non existent value, so null values will not be confused with "undefined"
 * @package McValidator\Data
 */
class NonExistentValue extends Value
{
    public function __construct(?State $state = null, ?Value $parent = null)
    {
        parent::__construct(null, null, $state, $parent);
    }
}