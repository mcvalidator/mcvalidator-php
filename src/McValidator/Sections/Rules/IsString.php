<?php


namespace McValidator\Sections\Rules;

use McValidator\Contracts\Section;
use McValidator\Data\Capsule;
use McValidator\Data\State;

class IsString extends Section
{
    /**
     * @param Capsule $capsule
     * @param State $state
     * @return Capsule
     * @throws \Exception
     */
    protected function receive(Capsule $capsule, State $state)
    {
        if (!is_string($capsule->getValue()->get())) {
            throw new \Exception("Value is not a string");
        }

        return $capsule;
    }
}