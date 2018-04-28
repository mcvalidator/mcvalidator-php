<?php

namespace McValidator\Support;

use Heterogeny\Seq;
use McValidator\Contracts\Pipeable;
use McValidator\Contracts\Section;
use McValidator\Data\Field;
use McValidator\Base;
use McValidator\Pipe;

class ListOf
{
    /**
     * @param array $sections
     * @return Builder
     * @throws \Exception
     */
    public static function build($sections): Builder
    {
        if (!is_array($sections) && !$sections instanceof Seq) {
            throw new \InvalidArgumentException("\$sections must be `array` or `\Heterogeny\Seq`");
        }

        foreach ($sections as $section) {
            Section::isValidOrFail($section);
        }

        return new ListOfBuilder(function (Field $field, ?Pipeable $parent) use ($sections): Pipeable {
            $pipe = new Pipe($field, $parent);

            $section = Base::getSection('rule@is-list-of');

            $pipe->add($section, $sections);

            return $pipe;
        });
    }
}