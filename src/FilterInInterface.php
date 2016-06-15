<?php

/*
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

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
