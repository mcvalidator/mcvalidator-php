<?php


namespace McValidator\Support;

use Heterogeny\Dict;
use McValidator\Base;
use McValidator\Contracts\Pipeable;
use McValidator\Contracts\Section;
use McValidator\Data\Field;
use McValidator\Pipe;

class ShapeOfBuilder implements Builder
{
    private $sections;

    private $options;

    public function __construct(
        Dict $options,
        $sections
    )
    {
        $this->options = $options;
        $this->sections = $sections;
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

        return new ShapeOfBuilder($newOptions, $this->sections);
    }

    /**
     * @param $name
     * @return ShapeOfBuilder
     */
    public function del($name)
    {
        $newOptions = $this->options->del($name)->dict();

        return new ShapeOfBuilder($newOptions, $this->sections);
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

        $sections = $this->sections;

        $options = $this->options->map(function ($key, $value) use ($field, $pipe) {
            if ($value instanceof Builder) {
                $currentField = new Field($key, $field);

                return $value->build($currentField, $pipe);
            }

            return $value;
        });

        if (count($sections) > 0) {
            $otherPipe = Pipe::build($field, $parent, $sections);

            $pipe->add($otherPipe);
        }

        $section = Base::getSection(Base::name('rule', 'is-shape-of'));

        $pipe->add($section, $options);

        return $pipe;
    }
}