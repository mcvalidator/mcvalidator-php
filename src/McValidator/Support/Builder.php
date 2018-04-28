<?php


namespace McValidator\Support;

use McValidator\Contracts\Pipeable;
use McValidator\Data\Field;

interface Builder
{
    function build(?Field $field = null, ?Pipeable $parent = null): Pipeable;
}