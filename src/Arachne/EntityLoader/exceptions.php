<?php

/**
 * This file is part of the EntityLoader extenstion
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and licence information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

/**
 * The exception that is thrown when a method call is invalid for the object's
 * current state, method has been invoked at an illegal or inappropriate time.
 */
class InvalidStateException extends \RuntimeException
{
}
