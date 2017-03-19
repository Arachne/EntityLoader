<?php

declare(strict_types=1);

namespace Tests\Functional\Fixtures;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class Article
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}
