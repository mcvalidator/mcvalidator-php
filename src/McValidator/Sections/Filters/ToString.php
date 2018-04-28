<?php


namespace McValidator\Sections\Filters;


use McValidator\Contracts\Section;
use McValidator\Data\Capsule;
use McValidator\Data\State;

class ToString extends Section
{
    protected function receive(Capsule $capsule, State $state)
    {
        return $capsule->newValue(function ($value) {
            return (string)$value;
        });
    }
}