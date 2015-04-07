<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class TypeDetector extends Object implements TypeDetectorInterface
{

	/**
	 * @param object $object
	 * @return string $string
	 */
	public function detectType($object)
	{
		return get_class($object);
	}

}
