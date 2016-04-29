<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\Application;

use Arachne\EntityLoader\EntityUnloader;
use Nette\Application\Request;
use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityUnloader extends Object
{
    /** @var EntityUnloader */
    private $entityUnloader;

    /**
     * @param EntityUnloader $entityUnloader
     */
    public function __construct(EntityUnloader $entityUnloader)
    {
        $this->entityUnloader = $entityUnloader;
    }

    /**
     * @param Request $request
     * @param bool $envelopes
     */
    public function filterOut(Request $request, $envelopes = false)
    {
        $parameters = $request->getParameters();
        foreach ($parameters as &$value) {
            if (is_object($value)) {
                $parameter = $this->entityUnloader->filterOut($value);
                $value = $envelopes ? new Envelope($value, $parameter) : $parameter;
            }
        }
        $request->setParameters($parameters);
    }
}
