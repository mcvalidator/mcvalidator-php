<?php

namespace McValidator\Parser;

use McValidator\Contracts\Pipeable;
use McValidator\Support\Builder;

class Registry
{
    private $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
    }

    public function get($name): Builder
    {
        if (!key_exists($name, $this->registry)) {
            throw new \Exception(
                sprintf(
                    "Root builder $name does not exists, available: %s",
                    join(', ', array_keys($this->registry))
                )
            );
        }

        return $this->registry[$name];
    }

    public function build($name): Pipeable
    {
        return $this->get($name)->build();
    }
}