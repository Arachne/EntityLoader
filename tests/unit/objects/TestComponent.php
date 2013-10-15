<?php

namespace Tests\Unit;

use Exception;
use Nette\Application\UI\PresenterComponent;

class TestComponent extends PresenterComponent
{

	/**
	 * @persistent
	 * @var Class5
	 */
	public $persistent;

	final public function __construct()
	{
		throw new Exception('This class is there for annotations only.');
	}

	public function handleTestHandle(Class6 $handleEntity)
	{
	}

}
