<?php


namespace McValidator\Contracts;

use McValidator\Data\Value;
use McValidator\Data\State;

interface Pumps
{
    function receive(Value $value, State $state): Value;

    function pump($value, ?State $state): Value;
}