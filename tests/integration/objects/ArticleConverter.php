<?php

namespace Tests\Integration;

use Arachne\EntityLoader\IConverter;
use InvalidArgumentException;
use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class ArticleConverter extends Object implements IConverter
{

	public function entityToParameter($type, $entity)
	{
		if ($type !== 'Tests\Integration\Article') {
			throw new InvalidArgumentException("String '$type' is not allowed type.");
		}
		if (!$entity instanceof $type) {
			throw new InvalidArgumentException("Entity is not an instance of '$type'.");
		}
		return $entity->getValue();
	}

	public function parameterToEntity($type, $value)
	{
		if ($type !== 'Tests\Integration\Article') {
			throw new InvalidArgumentException("String '$type' is not allowed type.");
		}
		return new Article($value);
	}

}
