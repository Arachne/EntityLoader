<?php

namespace Tests\Functional\Classes;

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
