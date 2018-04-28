<?php

namespace McValidator\Data;

use Heterogeny\Seq;
use McValidator\Contracts\Pipeable;
use McValidator\Contracts\Section;

class State
{
    /**
     * @var Pipeable
     */
    protected $source;

    /**
     * @var \Heterogeny\Seq
     */
    protected $errors;

    /**
     * @var \Heterogeny\Seq
     */
    protected $messages;

    /**
     * @var mixed
     */
    protected $others;

    public function __construct(Pipeable $source, $others = null)
    {
        $this->source = $source;
        $this->errors = seq();
        $this->messages = seq();
        $this->others = $others;
    }

    public function addError(Field $field, $message, Section $section)
    {
        $this->errors = $this->errors->append(new Error(
            $field,
            $message,
            $section
        ));
    }

    public function merge(State $other)
    {
        $this->errors = $this->errors->appendAll($other->getErrors());
        $this->messages = $this->messages->appendAll($other->getMessages());

        return $this;
    }

    public function prefixWith(Field $parent)
    {
        $this->errors = $this->errors->map(function (Error $error) use ($parent) {
            $field = $error->getField()->setParent($parent);

            return $error->setField($field);
        });

        return $this;
    }

    public function getOthers()
    {
        return $this->others;
    }

    /**
     * @return Pipeable
     */
    public function getSource(): Pipeable
    {
        return $this->source;
    }

    public function getErrors(): Seq
    {
        return $this->errors;
    }

    public function getMessages(): Seq
    {
        return $this->messages;
    }
}