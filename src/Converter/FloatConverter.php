<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\Converter;

use Arachne\EntityLoader\ConverterInterface;
use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class FloatConverter extends Object implements ConverterInterface
{

	public function filterIn($type, $value)
	{
		return (float) $value;
	}

	public function filterOut($type, $value)
	{
		return (string) $value;
	}

}
