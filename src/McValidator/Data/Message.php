<?php


namespace McValidator\Data;


final class Message
{
    private $message;
    private $field;

    public function __construct(Field $field, $message)
    {
        $this->field = $field;
        $this->message = $message;
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
     * @return Message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param Field $field
     * @return Message
     */
    public function setField(Field $field): Message
    {
        $this->field = $field;
        return $this;
    }
}