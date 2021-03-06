<?php


namespace McValidator\Sections\Filters;


use McValidator\Contracts\Section;
use McValidator\Data\Capsule;

class ToString extends Section
{
    protected function receive(Capsule $capsule)
    {
        return $capsule->newValue(function ($value) {
            return (string)$value;
        });
    }
}