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
        return $this->getFilter(get_class($object))->filterOut($object);
    }

    private function getFilter(string $class): FilterOutInterface
    {
        if (isset($this->filterMap[$class])) {
            return $this->filterMap[$class];
        }

        /** @var FilterOutInterface $filter */
        foreach ($this->filterOutIterator as $filter) {
            if ($filter->supports($class)) {
                return $this->filterMap[$class] = $filter;
            }
        }

        throw new UnexpectedValueException(sprintf('No filter out found for class "%s".', $class));
    }
}
