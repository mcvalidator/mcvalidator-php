<?php


namespace McValidator\Contracts;

use McValidator\Data\Value;
use McValidator\Data\State;

interface Pumps
{
    function receive(Value $value): Value;

    function pump($value): Value;
}