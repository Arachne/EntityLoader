<?php

namespace Tests\Integration\Classes;

use Arachne\EntityLoader\FilterInInterface;
use InvalidArgumentException;
use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArticleFilterIn extends Object implements FilterInInterface
{

	public function filterIn($type, $value)
	{
		if ($type !== __NAMESPACE__ . '\\Article') {
			throw new InvalidArgumentException("String '$type' is not allowed type.");
		}
		return new Article($value);
	}

}
