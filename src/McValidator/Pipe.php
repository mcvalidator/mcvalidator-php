<?php


namespace McValidator;

use Heterogeny\Seq;
use McValidator\Contracts\Pipeable;
use McValidator\Contracts\Splitter;
use McValidator\Data\Field;
use McValidator\Data\SectionDefinition;
use McValidator\Data\State;
use McValidator\Data\Value;
use McValidator\Support\Builder;

final class Pipe extends Pipeable
{
    private $sections;

    public function __construct($field, $parent)
    {
        parent::__construct($field, $parent);

        $this->sections = seq();
    }

    public function add($section, $options = null)
    {
        $this->sections = $this->sections->append(new Splitter($this->field, $this, $section, $options));

        return $this;
    }

    public function addAll(array $sections)
    {
        foreach ($sections as $section) {
            $this->add($section);
        }

        return $this;
    }

    function receive(Value $value, State $state): Value
    {
        return $this->sections->foldLeft(function ($value, Splitter $item) use ($state) {
            return $item->receive($value, $state);
        }, $value);
    }

    /**
     * @param Field $field
     * @param Pipeable|null $parent
     * @param array $sections
     * @return Pipe
     * @throws \Exception
     */
    public static function build(Field $field, ?Pipeable $parent = null, $sections): Pipe
    {
        if (!is_array($sections) && !$sections instanceof Seq) {
            throw new \InvalidArgumentException("\$sections must be `array` or `\Heterogeny\Seq`");
        }

        $pipe = new Pipe($field, $parent);

        foreach ($sections as $section) {
            $options = null;

            if (is_string($section)) {
                $section = Base::getSection($section);
            }

            if ($section instanceof SectionDefinition) {
                $options = $section->getOptions();
                $section = $section->getSection();
            }

            if ($section instanceof Builder) {
                $section = $section->build($field, $parent);
            }

            $pipe->add($section, $options);
        }

        return $pipe;
    }
}