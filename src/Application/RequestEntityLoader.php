<?php

namespace Arachne\EntityLoader\Application;

use Arachne\EntityLoader\EntityLoader;
use Arachne\EntityLoader\Exception\UnexpectedValueException;
use Nette\Application\Request;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityLoader
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

    public function filterIn(Request $request): void
    {
        $mapping = $this->finder->getMapping($request);
        $parameters = $request->getParameters();
        foreach ($mapping as $name => $info) {
            if (!isset($parameters[$name])) {
                if ($info->optional) {
                    continue;
                }
                throw new UnexpectedValueException("Parameter '$name' can't be null.");
            }
            $parameters[$name] = $this->entityLoader->filterIn($info->type, $parameters[$name]);
        }
        $request->setParameters($parameters);
    }
}
