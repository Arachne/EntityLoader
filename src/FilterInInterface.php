<?php

namespace Arachne\EntityLoader;

use Nette\Application\BadRequestException;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
interface FilterInInterface
{
    /**
     * @param string $type
     *
     * @return bool
     */
    public function supports(string $type): bool;

    /**
     * @param mixed $value
     *
     * @throws BadRequestException
     *
     * @return mixed
     */
    public function filterIn($value);
}
