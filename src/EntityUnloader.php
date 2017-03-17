<?php

namespace Arachne\EntityLoader;

use Arachne\EntityLoader\Exception\UnexpectedValueException;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityUnloader
{
    /**
     * @var callable
     */
    private $filterOutResolver;

    public function __construct(callable $filterOutResolver)
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

    private function getFilter(string $type): FilterOutInterface
    {
        $filter = ($this->filterOutResolver)($type);
        if (!$filter) {
            throw new UnexpectedValueException("No filter out found for type '$type'.");
        }

        return $filter;
    }
}
