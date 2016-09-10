<?php

/*
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\Application;

use Arachne\EntityLoader\EntityUnloader;
use Nette\Application\Request;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityUnloader
{
    /**
     * @var EntityUnloader
     */
    private $entityUnloader;

    public function __construct(EntityUnloader $entityUnloader)
    {
        $this->entityUnloader = $entityUnloader;
    }

    public function filterOut(Request $request, bool $envelopes = false)
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
