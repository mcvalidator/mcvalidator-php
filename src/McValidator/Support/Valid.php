<?php

namespace McValidator\Support;

use Heterogeny\Seq;
use McValidator\Contracts\Section;

class Valid
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

        return new ValidBuilder($sections);
    }
}