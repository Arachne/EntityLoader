<?php

declare(strict_types=1);

namespace Arachne\EntityLoader;

use Arachne\EntityLoader\Exception\UnexpectedValueException;
use Traversable;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityLoader
{
    /**
     * @var Traversable
     */
    private $filterInIterator;

    /**
     * @var array
     */
    private $filterMap;

    public function __construct(Traversable $filterInIterator)
    {
        $this->filterInIterator = $filterInIterator;
    }

    /**
     * @param string $type
     * @param mixed  $parameter
     *
     * @return mixed
     */
    public function filterIn(string $type, $parameter)
    {
        if ($this->isType($type, $parameter)) {
            return $parameter;
        }
        $value = $this->getFilter($type)->filterIn($parameter, $type);
        if (!$this->isType($type, $value)) {
            throw new UnexpectedValueException(sprintf('FilterIn did not return an instance of "%s".', $type));
        }

        return $value;
    }

    private function getFilter(string $type): FilterInInterface
    {
        if (isset($this->filterMap[$type])) {
            return $this->filterMap[$type];
        }

        /** @var FilterInInterface $filter */
        foreach ($this->filterInIterator as $filter) {
            if ($filter->supports($type)) {
                return $this->filterMap[$type] = $filter;
            }
        }

        throw new UnexpectedValueException(sprintf('No filter in found for type "%s".', $type));
    }

    /**
     * @param string $type
     * @param mixed  $value
     *
     * @return bool
     */
    private function isType(string $type, $value): bool
    {
        switch ($type) {
            case 'int':
                return is_int($value);
            case 'float':
                return is_float($value);
            case 'bool':
                return is_bool($value);
            case 'string':
                return is_string($value);
            case 'array':
                return is_array($value);
            case 'object':
                return is_object($value);
            case 'resource':
                return is_resource($value);
            case 'callable':
                return is_callable($value);
            case 'mixed':
                return true;
            default:
                return $value instanceof $type;
        }
    }
}
