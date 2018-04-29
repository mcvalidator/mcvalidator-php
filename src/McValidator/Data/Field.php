<?php


namespace McValidator\Data;


final class Field
{
    private $name;

    /**
     * @var Field|null
     */
    private $parent;

    /**
     * @param $name
     * @param Field|null $parent
     */
    public function __construct($name = '$', ?Field $parent = null)
    {
        $this->name = $name;
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Field
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Field|null
     */
    public function getParent(): ?Field
    {
        return $this->parent;
    }

    /**
     * @param Field|null $parent
     *
     * @return Field
     */
    public function setParent(?Field $parent): Field
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getPath(): array
    {
        if ($this->parent) {
            return array_merge($this->parent->getPath(), [$this->name]);
        }

        return [$this->name];
    }

    public function noRoot()
    {
        if ($this->isRoot()) {
            return null;
        }

        if ($this->parent === null) {
            return $this;
        }

        if ($this->parent !== null && $this->parent->isRoot()) {
            $this->parent = null;
        } else {
            $this->parent = $this->parent->noRoot();
        }

        return $this;
    }

    public function isRoot()
    {
        return $this->name === '$';
    }
}