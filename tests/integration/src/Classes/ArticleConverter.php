<?php

namespace Tests\Integration\Classes;

use Arachne\EntityLoader\IConverter;
use InvalidArgumentException;
use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class ArticleConverter extends Object implements IConverter
{

	public function canConvert($type)
	{
		return $type === __NAMESPACE__ . '\\Article';
	}

	public function entityToParameter($type, $entity)
	{
		if ($type !== __NAMESPACE__ . '\\Article') {
			throw new InvalidArgumentException("String '$type' is not allowed type.");
		}
		if (!$entity instanceof $type) {
			throw new InvalidArgumentException("Entity is not an instance of '$type'.");
		}
		return $entity->getValue();
	}

	public function parameterToEntity($type, $value)
	{
		if ($type !== __NAMESPACE__ . '\\Article') {
			throw new InvalidArgumentException("String '$type' is not allowed type.");
		}
		return new Article($value);
	}

}
