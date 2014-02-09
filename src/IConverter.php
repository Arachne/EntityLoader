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
interface IConverter
{

	/**
	 * @param string $type
	 * @return bool
	 */
	public function canConvert($type);

	/**
	 * @param string $type
	 * @param mixed $value
	 * @return object
	 * @throws BadRequestException
	 */
	public function parameterToEntity($type, $value);

	/**
	 * @param string $type
	 * @param object $entity
	 * @return string
	 */
	public function entityToParameter($type, $entity);

}
