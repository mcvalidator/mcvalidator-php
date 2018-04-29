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

    /**
     * @param Field|null $field
     * @param Pipeable|null $parent
     * @return Pipeable
     * @throws \Exception
     */
    public function build(?Field $field = null, ?Pipeable $parent = null): Pipeable
    {
        if ($field === null) {
            $field = new Field('$');
        }

        return Pipe::build($field, $parent, $this->sections);
    }
}