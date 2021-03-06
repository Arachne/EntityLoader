<?php

declare(strict_types=1);

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

    /**
     * {@inheritdoc}
     */
    public function filterOut($value)
    {
        if (!$value instanceof Article) {
            throw new InvalidArgumentException(sprintf('Entity is not an instance of "%s".', Article::class));
        }

        return $value->getValue();
    }
}
