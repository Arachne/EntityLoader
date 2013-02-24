<?php

namespace ArachneTests\EntityLoader;

class TestComponentOne extends \Nette\Application\UI\PresenterComponent
{

	/**
	 * @persistent
	 * @Entity mapping
	 */
	public $persistent;

	final public function __construct()
	{
		throw new \Exception('This class is there for annotations only.');
	}

	/**
	 * @Entity handle $handleEntity
	 */
	public function handleTestHandle($handleEntity)
	{
	}

}
