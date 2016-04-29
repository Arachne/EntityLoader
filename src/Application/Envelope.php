<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\Application;

use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class Envelope extends Object
{
    /** @var object */
    private $object;

    /** @var string|int */
    private $identifier;

    /**
     * @param object $object
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
