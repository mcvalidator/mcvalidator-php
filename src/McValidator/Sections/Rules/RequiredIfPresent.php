<?php


namespace McValidator\Sections\Rules;

use McValidator\Contracts\Section;
use McValidator\Data\Capsule;
use McValidator\Data\NonExistentValue;

class RequiredIfPresent extends Section
{
    protected $required = true;

    /**
     * @param Capsule $capsule
     * @return Capsule
     * @throws \Exception
     */
    protected function receive(Capsule $capsule)
    {
        $value = $capsule->getValue();

        $path = $capsule->getOptions()->getValue();

        $targetValue = $value->walk($path);

        if (!$targetValue instanceof NonExistentValue) {
            if ($value instanceof NonExistentValue) {
                $p = $capsule->getField()->getStringPath();
                throw new \Exception("Value `$p` is required if `$path` is present");
            }
        }

        return $capsule;
    }
}