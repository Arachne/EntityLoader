<?php

namespace Arachne\EntityLoader\FilterIn;

use Arachne\EntityLoader\FilterInInterface;
use Nette\Application\BadRequestException;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FloatFilterIn implements FilterInInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $type): bool
    {
        return $type === 'float';
    }

    /**
     * {@inheritdoc}
     */
    public function filterIn($value)
    {
        if (!is_string($value)) {
            throw new BadRequestException();
        }

        return (float) $value;
    }
}
