<?php


namespace McValidator\Data;


/**
 * Representation of an invalid value
 * @package McValidator\Data
 */
class InvalidValue extends Value
{
    public function isValid()
    {
        return false;
    }
}