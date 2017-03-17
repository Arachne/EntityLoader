<?php

namespace Arachne\EntityLoader;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
interface FilterOutInterface
{
    /**
     * @param string $class
     *
     * @return bool
     */
    public function supports(string $class): bool;

    /**
     * @param mixed $value
     *
     * @return string|array
     */
    public function filterOut($value);
}
