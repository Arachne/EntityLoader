<?php

namespace Arachne\EntityLoader;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
interface FilterOutInterface
{
    /**
     * @param mixed $value
     *
     * @return string|array
     */
    public function filterOut($value);
}
