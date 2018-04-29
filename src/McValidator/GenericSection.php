<?php


namespace McValidator;


use McValidator\Contracts\Section;
use McValidator\Data\Capsule;
use McValidator\Data\State;
use SuperClosure\Analyzer\AstAnalyzer;
use SuperClosure\Serializer;

final class GenericSection extends Section implements \Serializable
{
    /**
     * @var callable
     */
    private $fn;

    /**
     * GenericSection constructor.
     * @param $identifier
     * @param callable $fn
     */
    public function __construct($identifier, callable $fn)
    {
        parent::__construct($identifier);

        $this->fn = $fn;
    }

    /**
     * @param Capsule $capsule
     * @param State $state
     * @return Capsule
     */
    protected function receive(Capsule $capsule)
    {
        $fn = $this->fn;

        return $fn($capsule);
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        $serializer = new Serializer(new AstAnalyzer());

        return serialize([
            'n' => $this->identifier,
            's' => $serializer->serialize($this->fn)
        ]);
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);

        $serializer = new Serializer(new AstAnalyzer());

        $this->identifier = $unserialized['n'];
        $this->fn = $serializer->unserialize($unserialized['s']);
    }
}