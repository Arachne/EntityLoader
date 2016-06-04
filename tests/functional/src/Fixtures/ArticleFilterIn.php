<?php

namespace Tests\Functional\Fixtures;

use Arachne\EntityLoader\FilterInInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArticleFilterIn implements FilterInInterface
{
    public function filterIn($value)
    {
        return new Article($value);
    }
}
