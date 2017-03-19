<?php

declare(strict_types=1);

namespace Arachne\EntityLoader\Application;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class Envelope
{
    /**
     * @var object
     */
    private $object;

    /**
     * @var string|int
     */
    private $identifier;

    /**
     * @param object     $object
     * @param string|int $identifier
     */
    public function __construct($object, $identifier)
    {
        $this->object = $object;
        $this->identifier = $identifier;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return string|int
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}
