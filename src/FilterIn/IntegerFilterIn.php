<?php

namespace Arachne\EntityLoader\FilterIn;

use Arachne\EntityLoader\FilterInInterface;
use Nette\Application\BadRequestException;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class IntegerFilterIn implements FilterInInterface
{
    /**
     * {@inheritdoc}
     */
    public function filterIn($value)
    {
        if (!is_string($value)) {
            throw new BadRequestException();
        }

        return (int) $value;
    }
}
