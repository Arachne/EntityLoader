<?php

namespace Arachne\EntityLoader;

use Nette\Application\BadRequestException;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
interface FilterInInterface
{
    /**
     * @param mixed $value
     *
     * @throws BadRequestException
     *
     * @return mixed
     */
    public function filterIn($value);
}
