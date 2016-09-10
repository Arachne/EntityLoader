<?php

/*
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

use Arachne\DIHelpers\ResolverInterface;
use Arachne\EntityLoader\Exception\UnexpectedValueException;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityUnloader
{
    /**
     * @var ResolverInterface
     */
    private $filterOutResolver;

    public function __construct(ResolverInterface $filterOutResolver)
    {
        $this->filterOutResolver = $filterOutResolver;
    }

    /**
     * @param object $object
     *
     * @return string|array
     */
    public function filterOut($object)
    {
        $type = $object instanceof EntityInterface ? $object->getBaseType() : get_class($object);

        return $this->getFilter($type)->filterOut($object);
    }

    private function getFilter(string $type) : FilterOutInterface
    {
        $filter = $this->filterOutResolver->resolve($type);
        if (!$filter) {
            throw new UnexpectedValueException("No filter out found for type '$type'.");
        }

        return $filter;
    }
}
