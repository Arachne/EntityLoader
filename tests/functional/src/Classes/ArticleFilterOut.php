<?php

namespace Tests\Functional\Classes;

use Arachne\EntityLoader\FilterOutInterface;
use InvalidArgumentException;
use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArticleFilterOut extends Object implements FilterOutInterface
{
    public function filterOut($value)
    {
        if (!$value instanceof Article) {
            throw new InvalidArgumentException("Entity is not an instance of 'Article'.");
        }
        return $value->getValue();
    }
}
