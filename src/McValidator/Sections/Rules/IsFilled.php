<?php


namespace McValidator\Sections\Rules;

use McValidator\Contracts\Section;
use McValidator\Data\Capsule;

class IsFilled extends Section
{
    protected $required = true;

    /**
     * @param Capsule $capsule
     * @return Capsule
     * @throws \Exception
     */
    protected function receive(Capsule $capsule)
    {
        if (!$capsule->exists()) {
            throw new \Exception(
                "Could not ensure that `{$capsule->getField()->getStringPath()}` is filled"
            );
        }

        return $capsule;
    }
}