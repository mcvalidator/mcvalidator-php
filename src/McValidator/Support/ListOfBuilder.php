<?php


namespace McValidator\Support;

use McValidator\Base;
use McValidator\Contracts\Pipeable;
use McValidator\Data\Field;
use McValidator\Pipe;

class ListOfBuilder implements Builder
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

        $pipe = new Pipe($field, $parent);

        $section = Base::getSection(Base::name('rule', 'is-list-of'));

        $pipe->add($section, $this->sections);

        return $pipe;
    }
}