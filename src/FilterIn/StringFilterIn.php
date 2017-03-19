<?php

declare(strict_types=1);

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
    public function supports(string $type): bool
    {
        return $type === 'string';
    }

    /**
     * {@inheritdoc}
     */
    public function filterIn($value, string $type)
    {
        if (!is_string($value)) {
            throw new BadRequestException();
        }

        return $value;
    }
}
