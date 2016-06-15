<?php

/*
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
interface EntityInterface
{
    /**
     * @return string
     */
    public function getBaseType();
}
