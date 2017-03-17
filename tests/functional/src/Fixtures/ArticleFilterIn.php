<?php

namespace Tests\Functional\Fixtures;

use Arachne\EntityLoader\FilterInInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArticleFilterIn implements FilterInInterface
{
    public function supports(string $type): bool
    {
        return $type === Article::class;
    }

    public function filterIn($value)
    {
        return new Article($value);
    }
}
