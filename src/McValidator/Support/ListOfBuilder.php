<?php


namespace McValidator\Support;

use McValidator\Contracts\Pipeable;
use McValidator\Data\Field;

class ListOfBuilder implements Builder
{
    private $builder;

    public function __construct(callable $builder)
    {
        $this->builder = $builder;
    }

    public function build(?Field $field = null, ?Pipeable $parent = null): Pipeable
    {
        if ($field === null) {
            $field = new Field('$');
        }

        return ($this->builder)($field, $parent);
    }
}