<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\Application;

use Arachne\EntityLoader\EntityLoader;
use Arachne\EntityLoader\Exception\UnexpectedValueException;
use Nette\Application\Request;
use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityLoader extends Object
{
    /**
     * @var EntityLoader
     */
    private $entityLoader;

    /**
     * @var ParameterFinder
     */
    private $finder;

    public function __construct(EntityLoader $entityLoader, ParameterFinder $finder)
    {
        $this->entityLoader = $entityLoader;
        $this->finder = $finder;
    }

    /**
     * @param Request $request
     */
    public function filterIn(Request $request)
    {
        $mapping = $this->finder->getMapping($request);
        $parameters = $request->getParameters();
        foreach ($mapping as $name => $info) {
            if (!isset($parameters[$name])) {
                if ($info->nullable) {
                    continue;
                } else {
                    throw new UnexpectedValueException("Parameter '$name' can't be null.");
                }
            }
            $parameters[$name] = $this->entityLoader->filterIn($info->type, $parameters[$name]);
        }
        $request->setParameters($parameters);
    }
}
