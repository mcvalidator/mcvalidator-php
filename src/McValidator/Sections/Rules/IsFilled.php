<?php


namespace McValidator\Sections\Rules;

use McValidator\Contracts\Section;
use McValidator\Data\Capsule;
use McValidator\Data\State;

class IsFilled extends Section
{
    /**
     * @param Capsule $capsule
     * @param State $state
     * @return Capsule
     * @throws \Exception
     */
    protected function receive(Capsule $capsule, State $state)
    {
        if (!$capsule->exists()) {
            throw new \Exception("Could not ensure that value is filled");
        }

        return $capsule;
    }
}