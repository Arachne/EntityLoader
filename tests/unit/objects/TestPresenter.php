<?php

namespace Tests\Unit;

use Exception;
use Nette\Application\UI\Presenter;

/**
 * @author Jáchym Toušek
 */
class TestPresenter extends Presenter
{

	/**
	 * @persistent
	 * @var Class1
	 */
	public $persistent1;

	/**
	 * @persistent
	 * @var string
	 */
	public $persistent2;

	final public function __construct()
	{
		throw new Exception('This class is there for annotations only.');
	}

	public function actionTestAction(Class2 $actionEntity)
	{
	}

	public function renderTestRender(Class3 $renderEntity)
	{
	}

	public function handleTestHandle(Class4 $handleEntity)
	{
	}

	public function actionNonexistentParameter($entity)
	{
	}

	/**
	 * @return TestComponent
	 */
	protected function createComponentComponent()
	{
	}

	/**
	 * @return NonexistentComponent
	 */
	protected function createComponentNonexistentComponent()
	{
	}

}
