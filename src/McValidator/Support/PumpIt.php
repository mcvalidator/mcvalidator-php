<?php

namespace McValidator\Support;

use McValidator\Data\Value;
use McValidator\Data\State;

trait PumpIt
{
    public function pump($value, ?State $state = null): Value
    {
        if (!$state) {
            $state = new State($this);
        }

        if (!$value instanceof Value) {
            $value = new Value($value, null, $state);
        }

        return $this->receive($value, $state);
    }
}