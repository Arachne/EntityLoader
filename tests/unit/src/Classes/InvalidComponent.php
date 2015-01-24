<?php

namespace Tests\Unit\Classes;

use Exception;
use Nette\Application\UI\PresenterComponent;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class InvalidComponent extends PresenterComponent
{

	/**
	 * @persistent
	 * @var $invalid
	 */
	public $persistent;

	final public function __construct()
	{
		throw new Exception('This class is there for annotations only.');
	}

}
