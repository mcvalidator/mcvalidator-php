<?php


namespace McValidator\Sections\Rules;

use McValidator\Contracts\Section;
use McValidator\Data\Capsule;

class IsString extends Section
{
    /**
     * @param Capsule $capsule
     * @return Capsule
     * @throws \Exception
     */
    protected function receive(Capsule $capsule)
    {
        if (!is_string($capsule->getValue()->get())) {
            throw new \Exception("Value is not a string");
        }

        return $capsule;
    }
}