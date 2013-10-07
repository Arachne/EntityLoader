<?php

/**
 * This file is part of the Arachne EntityLoader extenstion
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

/**
 * @author Jáchym Toušek
 */
interface IConverter
{

	/**
	 * @param string $type
	 * @param mixed $value
	 * @return mixed
	 */
	public function parameterToEntity($type, $value);

	/**
	 * @param string $type
	 * @param mixed $entity
	 * @return mixed
	 */
	public function entityToParameter($type, $entity);

}
