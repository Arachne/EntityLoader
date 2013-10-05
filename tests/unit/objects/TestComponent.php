<?php

namespace Tests;

use Arachne\EntityLoader\Entity;
use Exception;
use Nette\Application\UI\PresenterComponent;

class TestComponent extends PresenterComponent
{

	/**
	 * @persistent
	 * @Entity(entity="mapping")
	 */
	public $persistent;

	final public function __construct()
	{
		throw new Exception('This class is there for annotations only.');
	}

	/**
	 * @Entity(entity="handle", parameter="handleEntity")
	 */
	public function handleTestHandle($handleEntity)
	{
	}

}
