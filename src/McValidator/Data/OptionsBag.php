<?php


namespace McValidator\Data;

use Heterogeny\Dict;

final class OptionsBag
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @var bool
     */
    private $isDict = false;

    /**
     * @var Dict
     */
    private $options;

    /**
     * OptionsBag constructor.
     * @param mixed $options
     */
    public function __construct($options)
    {
        $this->value = $options;

        if (is_array($options)) {
            $this->options = dict($options);
        } else {
            $this->options = $options;
        }

        $this->isDict = $this->options instanceof Dict;
    }

    public function get($key)
    {
        if (!$this->isDict) {
            throw new \Exception('This OptionsBag is not a Dict, only holds 1 single value.');
        }

        return $this->options->get($key);
    }

    public function getOrElse($key, $default = null)
    {
        if (!$this->isDict) {
            throw new \Exception('This OptionsBag is not a Dict, only holds 1 single value.');
        }

        return $this->options->getOrElse($key, $default);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getValueOrElse($or = null)
    {
        if ($this->value !== null) {
            return $this->value;
        }

        return $or;
    }
}