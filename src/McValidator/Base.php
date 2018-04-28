<?php

namespace McValidator;

use McValidator\Contracts\Section;
use McValidator\Support\Str;

/**
 * @package McValidator
 */
final class Base
{
    /**
     * @var Section[]
     */
    private static $registry = [];

    private static $classPath = [
        'filter' => ['McValidator\\Sections\\Filters\\'],
        'rule' => ['McValidator\\Sections\\Rules\\']
    ];

    public static function createSection($name, callable $fn)
    {
        $section = new GenericSection($name, $fn);

        self::$registry[$name] = $section;

        return $section;
    }

    /**
     * @param $name
     * @param callable $fn
     * @return GenericSection
     */
    public static function createRule($name, callable $fn)
    {
        return self::createSection("rule@$name", $fn);
    }

    /**
     * @param $name
     * @param callable $fn
     * @return GenericSection
     */
    public static function createFilter($name, callable $fn)
    {
        return self::createSection("filter@$name", $fn);
    }

    public static function appendClassPath($namespace, $prefix)
    {
        if (!key_exists($namespace, self::$classPath)) {
            self::$classPath[$namespace] = [];
        }

        self::$classPath[$namespace][] = $prefix;
    }

    public static function preprendClassPath($namespace, $prefix)
    {
        if (!key_exists($namespace, self::$classPath)) {
            self::$classPath[$namespace] = [];
        }

        array_unshift(self::$classPath[$namespace], $prefix);
    }

    /**
     * @param $sectionClass
     * @param null $alias
     * @return mixed
     */
    public static function register($sectionClass, $alias)
    {
        $instance = new $sectionClass($alias);

        self::$registry[$sectionClass] = $instance;
        self::$registry[$alias] = $instance;

        return $instance;
    }

    /**
     * @param $namespace
     * @param $alias
     * @return Section
     * @throws \Exception
     */
    private static function findSection($namespace, $alias)
    {
        if (!key_exists($namespace, self::$classPath)) {
            throw new \Exception("Cannot find namespace `$namespace`");
        }

        $namespaceClassPath = self::$classPath[$namespace];

        if (!is_array($namespaceClassPath)) {
            $namespaceClassPath = [$namespaceClassPath];
        }

        $tried = [];

        foreach ($namespaceClassPath as $prefix) {
            $className = Str::studly($alias);
            $class = "{$prefix}{$className}";

            if (class_exists($class)) {
                return self::register($class, "$namespace:$alias");
            }

            $tried[] = $class;
        }

        $tried = join("\n- ", $tried);

        throw new \Exception("Cannot find section `$namespace:$alias` tried:\n- $tried\n\n");
    }

    /**
     * @param $section
     * @return Section
     * @throws \Exception
     * @internal param $rule
     */
    public static function getSection($section)
    {
        if (key_exists($section, self::$registry)) {
            $result = self::$registry[$section];
        } else {
            $result = null;
        }

        if ($result === null) {
            $split = explode("@", $section);
            if (count($split) === 2) {
                list($namespace, $alias) = $split;

                $result = self::findSection($namespace, $alias);
            }
        }

        if (!$result instanceof Section) {
            throw new \Exception("No section $section was found on registry.");
        }

        return $result;
    }

}
