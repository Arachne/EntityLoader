<?php

namespace Arachne\EntityLoader\FilterIn;

use Arachne\EntityLoader\FilterInInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class MixedFilterIn implements FilterInInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $type): bool
    {
        return $type === 'mixed';
    }

    /**
     * {@inheritdoc}
     */
    public function filterIn($value)
    {
        return $value;
    }
}
