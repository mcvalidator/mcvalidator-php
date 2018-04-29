<?php


namespace McValidator\Sections\Filters;


use McValidator\Contracts\Section;
use McValidator\Data\Capsule;
use function McValidator\valid;

class ToInt extends Section
{
    public function setup()
    {
        $this->validation = valid('rule/is-int');
    }

    protected function receive(Capsule $capsule)
    {
        return $capsule->newValue(function ($value) {
            return (int)$value;
        });
    }
}