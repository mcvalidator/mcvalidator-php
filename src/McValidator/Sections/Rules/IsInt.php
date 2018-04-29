<?php


namespace McValidator\Sections\Rules;

use McValidator\Contracts\Section;
use McValidator\Data\Capsule;
use function McValidator\lazydebug;

class IsInt extends Section
{
    /**
     * @param Capsule $capsule
     * @return Capsule
     * @throws \Exception
     */
    protected function receive(Capsule $capsule)
    {
        if (!is_numeric($capsule->getValue()->get())) {
            throw new \Exception("Value is not an integer");
        }

        return $capsule;
    }
}