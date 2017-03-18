<?php

namespace Arachne\EntityLoader\FilterIn;

use Arachne\EntityLoader\FilterInInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArrayFilterIn implements FilterInInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $type): bool
    {
        return $type === 'array';
    }

    /**
     * {@inheritdoc}
     */
    public function filterIn($value, string $type)
    {
        return (array) $value;
    }
}
