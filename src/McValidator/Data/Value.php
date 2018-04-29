<?php


namespace McValidator\Data;

use Heterogeny\Dict;
use Heterogeny\Heterogenic;
use Heterogeny\Seq;
use McValidator\Contracts\Pipeable;
use McValidator\Data\State;

class Value
{
    private $value;

    private $oldValue;

    /**
     * @var State
     */
    private $state;

    /**
     * @param $value
     * @param Value|null $oldValue
     * @param Pipeable|null $source
     * @param State|null $state
     */
    public function __construct($value, $oldValue = null, ?State $state = null)
    {
        if ($state === null) {
            $state = new State();
        }

        if ($oldValue !== null && !$oldValue instanceof Value) {
            $oldValue = new Value($oldValue, null, $state);
        }

        $this->value = $value;
        $this->oldValue = $oldValue;
        $this->state = $state;
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

    public static function none(State $state): Value
    {
        return new Value(new NonExistentValue(), null, $state);
    }

}