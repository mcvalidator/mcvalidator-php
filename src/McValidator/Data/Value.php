<?php


namespace McValidator\Data;

use Heterogeny\Dict;
use Heterogeny\Heterogenic;
use Heterogeny\Seq;

class Value
{
    private $value;

    private $oldValue;

    /**
     * @var State
     */
    private $state;

    /**
     * @var Value
     */
    private $parent;

    /**
     * @param $value
     * @param Value|null $oldValue
     * @param State|null $state
     * @param Value|null $parent
     */
    public function __construct($value, $oldValue = null, ?State $state = null, ?Value $parent = null)
    {
        if ($state === null) {
            $state = new State();
        }

        if ($oldValue !== null && !$oldValue instanceof Value) {
            $oldValue = new Value($oldValue, null, $state, $parent);
        }

        $this->value = $value;
        $this->oldValue = $oldValue;
        $this->state = $state;
        $this->parent = $parent;
    }

    public function get($removeInvalid = true, $keepNulls = false, $keepEmpty = false)
    {
        if ($this instanceof InvalidValue && $removeInvalid) {
            return null;
        }

        if ($this->value instanceof Seq) {
            return $this->value->map(function ($value) use ($removeInvalid, $keepNulls, $keepEmpty) {
                if ($value instanceof Value) {
                    return $value->get($removeInvalid, $keepNulls, $keepEmpty);
                }

                return $value;
            })->filter(function ($value) use ($keepNulls, $keepEmpty) {
                if ($value instanceof Heterogenic) {
                    return $keepEmpty || !$value->isEmpty();
                }

                if ($keepNulls) {
                    return true;
                }

                return $value !== null;
            });
        }

        if ($this->value instanceof Dict) {
            return $this->value->map(function ($key, $value) use ($removeInvalid, $keepNulls, $keepEmpty) {
                if ($value instanceof Value) {
                    return $value->get($removeInvalid, $keepNulls, $keepEmpty);
                }

                return $value;
            })->filter(function ($key, $value) use ($keepNulls, $keepEmpty) {
                if ($value instanceof Heterogenic) {
                    return $keepEmpty || !$value->isEmpty();
                }

                if ($keepNulls) {
                    return true;
                }

                return $value !== null;
            });
        }


        return $this->value;
    }

    public function set($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return Value|null
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * @return State
     */
    public function getState(): State
    {
        return $this->state;
    }

    /**
     * @param State $state
     * @return Value
     */
    public function setState(State $state): Value
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @param State $state
     * @return Value
     */
    public function mergeState(State $state): Value
    {
        $this->state = $state->merge($this->state);

        return $this;
    }

    public function invalid()
    {
        return new InvalidValue($this->value, null, $this->state);
    }

    public static function none(State $state, ?Value $parent = null): NonExistentValue
    {
        return new NonExistentValue($state, $parent);
    }

    /**
     * @return Value
     */
    public function getParent(): ?Value
    {
        return $this->parent;
    }

    /**
     * @return Value
     */
    public function getValue()
    {
        return $this->value;
    }

    public function walk($path)
    {
        if (!is_array($path)) {
            $path = explode('/', $path);
        }

        $current = $this->parent;
        $value = Value::none($this->state, $this->parent);

        foreach ($path as $segment) {
            if ($current === null) break;

            if ($segment === '..') {
                $current = $current->getParent();
            } else {
                $possibleValue = $current->getValue();
                if ($possibleValue !== null) {
                    if ($possibleValue instanceof Heterogenic) {
                        $value = $possibleValue->getOrElse($segment, Value::none($this->state, $current));
                    }
                }
            }
        }

        if ($current === null) {
            return Value::none($this->state, $this);
        }

        if ($value instanceof NonExistentValue) {
            return $value;
        }

        return new Value($value, null, $this->state, $current);
    }
}