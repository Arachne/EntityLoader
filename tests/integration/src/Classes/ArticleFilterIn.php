<?php

namespace Tests\Integration\Classes;

use Arachne\EntityLoader\FilterInInterface;
use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArticleFilterIn extends Object implements FilterInInterface
{

	public function filterIn($value)
	{
		return new Article($value);
	}

}
