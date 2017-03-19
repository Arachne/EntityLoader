<?php

declare(strict_types=1);

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
     * @param mixed  $value
     * @param string $type
     *
     * @throws BadRequestException
     *
     * @return mixed
     */
    public function filterIn($value, string $type);
}
