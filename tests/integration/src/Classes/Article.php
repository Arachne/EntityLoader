<?php

namespace Tests\Integration\Classes;

use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class Article extends Object
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
