<?php

namespace Arachne\EntityLoader;

use Arachne\EntityLoader\Exception\UnexpectedValueException;
use Traversable;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityUnloader
{
    /**
     * @var Traversable
     */
    private $filterOutIterator;

    /**
     * @var array
     */
    private $filterMap;

    public function __construct(Traversable $filterOutIterator)
    {
        $this->filterOutIterator = $filterOutIterator;
    }

    /**
     * @param object $object
     *
     * @return string|array
     */
    public function filterOut($object)
    {
        $type = $object instanceof EntityInterface ? $object->getBaseType() : get_class($object);

        return $this->getFilter($type)->filterOut($object, $type);
    }

    private function getFilter(string $type): FilterOutInterface
    {
        if (isset($this->filterMap[$type])) {
            return $this->filterMap[$type];
        }

        /** @var FilterOutInterface $filter */
        foreach ($this->filterOutIterator as $filter) {
            if ($filter->supports($type)) {
                return $this->filterMap[$type] = $filter;
            }
        }

        throw new UnexpectedValueException(sprintf('No filter out found for type "%s".', $type));
    }
}
