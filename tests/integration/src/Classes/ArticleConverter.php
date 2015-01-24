<?php

namespace Tests\Integration\Classes;

use Arachne\EntityLoader\ConverterInterface;
use InvalidArgumentException;
use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArticleConverter extends Object implements ConverterInterface
{

	public function filterOut($type, $value)
	{
		if ($type !== __NAMESPACE__ . '\\Article') {
			throw new InvalidArgumentException("String '$type' is not allowed type.");
		}
		if (!$value instanceof $type) {
			throw new InvalidArgumentException("Entity is not an instance of '$type'.");
		}
		return $value->getValue();
	}

	public function filterIn($type, $value)
	{
		if ($type !== __NAMESPACE__ . '\\Article') {
			throw new InvalidArgumentException("String '$type' is not allowed type.");
		}
		return new Article($value);
	}

}
