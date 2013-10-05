<?php

/**
 * This file is part of the Arachne EntityLoader extenstion
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

use Nette\Object;

/**
 * @Annotation
 * @Target({"METHOD", "PROPERTY"})
 */
class Entity extends Object
{

	/** @var string */
	public $parameter;

	/** @var mixed */
	public $mapping;

}
