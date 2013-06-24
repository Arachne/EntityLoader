<?php

namespace Tests;

class TestComponentTwo extends \Nette\Application\UI\PresenterComponent
{

	/**
	 * @persistent
	 * @Entity (wrong, annotation)
	 */
	public $persistent;

	final public function __construct()
	{
		throw new \Exception('This class is there for annotations only.');
	}

}
