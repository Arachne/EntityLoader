<?php

namespace Tests;

use Arachne\EntityLoader\Entity;

class TestComponent extends \Nette\Application\UI\PresenterComponent
{

	/**
	 * @persistent
	 * @Entity(entity="mapping")
	 */
	public $persistent;

	final public function __construct()
	{
		throw new \Exception('This class is there for annotations only.');
	}

	/**
	 * @Entity(entity="handle", parameter="handleEntity")
	 */
	public function handleTestHandle($handleEntity)
	{
	}

}
