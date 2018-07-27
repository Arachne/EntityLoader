<?php

declare(strict_types=1);

namespace Tests\Functional\Fixtures;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class Article
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
