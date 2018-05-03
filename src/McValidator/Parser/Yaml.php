<?php

namespace McValidator\Parser;

use McValidator\Contracts\Section;
use McValidator\Data\SectionDefinition;
use Symfony\Component\Yaml\Tag\TaggedValue;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

class Yaml
{
    public static function parseSingle($yml)
    {
        $reg = SymfonyYaml::parse(
            $yml,
            SymfonyYaml::PARSE_CUSTOM_TAGS
            | SymfonyYaml::PARSE_OBJECT_FOR_MAP
        );

        return self::extract($reg);
    }

    public static function parse($yml): Registry
    {
        $structure = SymfonyYaml::parse(
            $yml,
            SymfonyYaml::PARSE_CUSTOM_TAGS
            | SymfonyYaml::PARSE_OBJECT_FOR_MAP
        );

        if (!$structure instanceof TaggedValue) {
            throw new \Exception("There must be a `!root` at the top of YAML.");
        }

        return self::extract($structure);
    }

    private static function extract($current)
    {
        if (is_array($current)) {
            $result = seq();

            foreach ($current as $section) {
                if (is_object($section)) {
                    foreach ((array)$section as $k => $v) {
                        $options = self::extract($v);
                        $definition = new SectionDefinition($k, $options);
                        $result = $result->append($definition);
                    }
                } else {
                    Section::isValidOrFail($section);
                    $result = $result->append($section);
                }
            }

            return \McValidator\Support\Valid::build($result);
        } elseif ($current instanceof TaggedValue) {
            switch ($current->getTag()) {
                case 'list-of':
                    return self::extractList($current->getValue());
                case 'shape-of':
                    return self::extractShape($current->getValue());
                case 'nullable-shape-of':
                    return self::extractNullableShape($current->getValue());
                case 'root':
                    $v = $current->getValue();
                    if (!key_exists('root', $v)) {
                        throw new \Exception("`!root` tag must be followed by a `root` object.");
                    }
                    return new Registry(self::extractRoot($v['root']));
            }
        } else if ($current instanceof \stdClass) {
            $result = dict();
            foreach ((array)$current as $key => $value) {
                if (strpos($key, '^') === 0) continue;
                $result = $result->set($key, self::extract($value));
            }
            return $result;
        } else if (is_string($current)) {
            Section::isValidOrFail($current);
            return $current;
        }

        return $current;
    }

    private static function extractRoot($value)
    {
        $output = [];

        foreach ((array)$value as $key => $value) {
            if (substr($key, 0, 1) === '^') continue;
            $output[$key] = self::extract($value);
        }

        return $output;
    }

    private static function extractShape($value)
    {
        if (!is_object($value)) {
            $value = (object)$value;
        }

        $result = dict();

        foreach ((array)$value as $k => $v) {
            if (substr($k, 0, 1) === '^' || $k === '_') continue;
            $result = $result->set($k, self::extract($v));
        }

        $options = [];

        if (property_exists($value, '_')) {
            $o = $value->_;

            // wraps into an array so extract can distinct it, else, extract will treat as simple options
            if (!is_array($o)) {
                $o = [$o];
            }

            $options = [self::extract($o)];
        }

        return \McValidator\Support\ShapeOf::build($result, ...$options);
    }



    private static function extractNullableShape($value)
    {
        if (!is_object($value)) {
            $value = (object)$value;
        }

        $result = dict();

        foreach ((array)$value as $k => $v) {
            if (substr($k, 0, 1) === '^' || $k === '_') continue;
            $result = $result->set($k, self::extract($v));
        }

        $options = [];

        if (property_exists($value, '_')) {
            $o = $value->_;

            // wraps into an array so extract can distinct it, else, extract will treat as simple options
            if (!is_array($o)) {
                $o = [$o];
            }

            $options = [self::extract($o)];
        }

        return \McValidator\Support\NullableShapeOf::build($result, ...$options);
    }

    private static function extractList($value)
    {
        $result = seq();

        foreach ((array)$value as $section) {
            if ($section instanceof stdClass) {
                foreach ((array)$section as $k => $v) {
                    $definition = new SectionDefinition($k, self::extract($v));
                    $result = $result->append($definition);
                }
            } else if ($section instanceof TaggedValue) {
                $result = $result->append(self::extract($section));
            } else {
                Section::isValidOrFail($section);
                $result = $result->append($section);
            }
        }

        return \McValidator\Support\ListOf::build($result);
    }
}