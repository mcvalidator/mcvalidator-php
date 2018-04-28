<?php


namespace McValidator;


use McValidator\Contracts\Section;
use McValidator\Data\Capsule;
use McValidator\Data\State;

final class GenericSection extends Section
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
    protected function receive(Capsule $capsule, State $state)
    {
        $fn = $this->fn;

        return $fn($capsule, $state);
    }
}