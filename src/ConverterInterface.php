<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

use Nette\Application\BadRequestException;

/**
 * @author Jáchym Toušek
 */
interface ConverterInterface
{

	/**
	 * @param string $type
	 * @param mixed $value
	 * @return object
	 * @throws BadRequestException
	 */
	public function filterIn($type, $value);

	/**
	 * @param string $type
	 * @param object $value
	 * @return string|string[]
	 */
	public function filterOut($type, $value);

}
