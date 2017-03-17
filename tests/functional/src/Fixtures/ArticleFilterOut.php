<?php

namespace Tests\Functional\Fixtures;

use Arachne\EntityLoader\FilterOutInterface;
use InvalidArgumentException;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArticleFilterOut implements FilterOutInterface
{
    public function supports(string $class): bool
    {
        return $class === Article::class;
    }

    public function filterOut($value)
    {
        if (!$value instanceof Article) {
            throw new InvalidArgumentException("Entity is not an instance of 'Article'.");
        }

        return $value->getValue();
    }
}
