<?php

namespace McValidator\Data;

use Heterogeny\Seq;
use Heterogeny\Utils;
use McValidator\Contracts\Section;

class State
{
    /**
     * @var \Heterogeny\Seq
     */
    protected $errors;

    /**
     * @var \Heterogeny\Seq
     */
    protected $messages;

    public function __construct(?Seq $errors = null, ?Seq $messages = null)
    {
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

        return $this;
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
            $errorField = $error->getField();

            $field = $errorField->setParent($parent);

            return $error->setField($field);
        });

        return $this;
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