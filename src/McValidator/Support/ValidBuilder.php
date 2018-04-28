<?php


namespace McValidator\Support;

use McValidator\Contracts\Pipeable;
use McValidator\Data\Field;
use McValidator\Pipe;

class ValidBuilder implements Builder
{
    private $sections;

    public function __construct($sections)
    {
        $this->sections = $sections;
    }

    public function build(?Field $field = null, ?Pipeable $parent = null): Pipeable
    {
        if ($field === null) {
            $field = new Field('$');
        }

        try {
            return Pipe::build($field, $parent, $this->sections);
        } catch (\Exception $e) {
        }
    }
}