<?php

namespace Arachne\EntityLoader\FilterIn;

use Arachne\EntityLoader\FilterInInterface;
use Nette\Application\BadRequestException;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class StringFilterIn implements FilterInInterface
{
    /**
     * {@inheritdoc}
     */
    public function filterIn($value)
    {
        if (!is_string($value)) {
            throw new BadRequestException();
        }

        return $value;
    }
}
