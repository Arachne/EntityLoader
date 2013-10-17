<?php

namespace Tests\Integration;

use Nette\Object;

class Article extends Object
{

	private $value;

	public function __construct($value)
	{
		$this->value = $value;
	}

	public function getValue()
	{
		return $this->value;
	}

}