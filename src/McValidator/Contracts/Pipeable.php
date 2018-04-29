<?php


namespace McValidator\Contracts;


use McValidator\Data\Field;
use McValidator\Support\PumpIt;

abstract class Pipeable implements Pumps
{
    use PumpIt;

    /**
     * @var Pipeable
     */
    protected $parent;

    /**
     * @var Field
     */
    protected $field;

    /**
     * Pipeable constructor.
     * @param $field
     * @param Pipeable $parent
     */
    public function __construct($field, $parent = null)
    {
        $parentField = null;
        if ($parent !== null && $parent instanceof Pipeable) {
            $parentField = $parent->getField();
            $this->parent = $parent;
        }

        if ($field === null) {
            $field = new Field(null, $parentField);
        }

        if (!$field instanceof Field) {
            $field = new Field($field, $parentField);
        }

        if ($parent instanceof Pipeable) {
            // This will avoid circular references
            if ($field !== $parent->getField()) {
                $field->setParent($parent->getField());
            }
        }

        $this->field = $field;
    }

    /**
     * @return Field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param Field $field
     * @return Pipeable
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return Pipeable
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Pipeable $parent
     * @return Pipeable
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        if ($this->field) {
            $this->field->setParent($parent->field);
        }

        return $this;
    }

    public function hasParent()
    {
        return $this->parent !== null;
    }
}