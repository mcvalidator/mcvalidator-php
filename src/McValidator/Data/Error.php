<?php


namespace McValidator\Data;


use McValidator\Contracts\Section;

final class Error
{
    private $message;
    private $field;
    private $section;

    public function __construct(Field $field, $message, Section $section)
    {
        $this->field = $field;
        $this->message = $message;
        $this->section = $section;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return Field
     */
    public function getField(): Field
    {
        return $this->field;
    }

    /**
     * @param mixed $message
     * @return Error
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param Field $field
     * @return Error
     */
    public function setField(Field $field): Error
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return Section
     */
    public function getSection(): Section
    {
        return $this->section;
    }

    /**
     * @param Section $section
     * @return Error
     */
    public function setSection(Section $section): Error
    {
        $this->section = $section;
        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getPath(): array
    {
        return $this->getField()->getPath();
    }

    /**
     * @param string $separator
     * @return mixed[]
     */
    public function getStringPath($separator = '/'): string
    {
        return join($separator, $this->getField()->getPath());
    }
}