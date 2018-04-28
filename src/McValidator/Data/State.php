<?php

namespace McValidator\Data;

use Heterogeny\Seq;
use Heterogeny\Utils;
use McValidator\Contracts\Pipeable;
use McValidator\Contracts\Section;
use McValidator\Data\Error;

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

    public function __construct(Pipeable $source, ?Seq $errors = null, ?Seq $messages = null)
    {
        $this->source = $source;
        $this->errors = $errors ?? seq();
        $this->messages = $messages ?? seq();
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

    public function ignoreErrors(array $path)
    {
        $path = Utils::arrayPrepend($path, '$');
        $joinedPath = join('|', $path);

        $newErrors = $this->errors->filter(function (Error $err) use ($joinedPath) {
            $fieldPath = join('|', $err->getField()->getPath());

            return strpos($fieldPath, $joinedPath) !== 0;
        });

        return new State(
            $this->source,
            $newErrors,
            $this->messages
        );
    }

    public function ignoreMessages(array $path)
    {
        $path = Utils::arrayPrepend($path, '$');
        $joinedPath = join('|', $path);

        $newMessages = $this->messages->filter(function (Error $err) use ($joinedPath) {
            $fieldPath = join('|', $err->getField()->getPath());

            return strpos($fieldPath, $joinedPath) !== 0;
        });

        return new State(
            $this->source,
            $this->errors,
            $newMessages
        );
    }

    public function hasError(array $path)
    {
        $path = Utils::arrayPrepend($path, '$');
        $joinedPath = join('|', $path);

        return !$this->errors->filter(function (Error $err) use ($joinedPath) {
            $fieldPath = join('|', $err->getField()->getPath());

            return strpos($fieldPath, $joinedPath) === 0;
        })->isEmpty();
    }

    public function hasMessage(array $path)
    {
        $path = Utils::arrayPrepend($path, '$');
        $joinedPath = join('|', $path);

        return !$this->messages->filter(function (Error $err) use ($joinedPath) {
            $fieldPath = join('|', $err->getField()->getPath());

            return strpos($fieldPath, $joinedPath) === 0;
        })->isEmpty();
    }
}