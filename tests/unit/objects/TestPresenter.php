<?php

namespace Tests;

use Arachne\EntityLoader\Entity;
use Exception;
use Nette\Application\UI\Presenter;

class TestPresenter extends Presenter
{

	/**
	 * @persistent
	 * @Entity(entity="persistent")
	 */
	public $persistent;

	final public function __construct()
	{
		throw new Exception('This class is there for annotations only.');
	}

	/**
	 * @Entity(entity="action", property="id", parameter="actionEntity")
	 */
	public function actionTestAction($actionEntity)
	{
	}

	/**
	 * @Entity(entity="render", property="id", parameter="renderEntity")
	 */
	public function renderTestRender($renderEntity)
	{
	}

	/**
	 * @Entity(entity="handle", property="id", parameter="handleEntity")
	 */
	public function handleTestHandle($handleEntity)
	{
	}

	/**
	 * @Entity(entity="table", property="id", parameter="nonexistent")
	 */
	public function actionNonexistentPatameter($entity)
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
