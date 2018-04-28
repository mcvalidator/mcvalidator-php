<?php


namespace McValidator\Support;

use Heterogeny\Dict;
use McValidator\Contracts\Pipeable;
use McValidator\Contracts\Section;
use McValidator\Data\Field;

class ShapeOfBuilder implements Builder
{
    private $builder;

    private $options;

    public function __construct(Dict $options, callable $builder)
    {
        $this->options = $options;
        $this->builder = $builder;
    }

    /**
     * @param $name
     * @param $section
     * @return ShapeOfBuilder
     * @throws \Exception
     */
    public function set($name, $section)
    {
        Section::isValidOrFail($section);

        $newOptions = $this->options->set($name, $section);

        return new ShapeOfBuilder($newOptions, $this->builder);
    }

    /**
     * @param $name
     * @return ShapeOfBuilder
     */
    public function del($name)
    {
        $newOptions = $this->options->del($name)->dict();

        return new ShapeOfBuilder($newOptions, $this->builder);
    }

    public function build(?Field $field = null, ?Pipeable $parent = null): Pipeable
    {
        if ($field === null) {
            $field = new Field('$');
        }

        return ($this->builder)($this->options, $field, $parent);
    }
}