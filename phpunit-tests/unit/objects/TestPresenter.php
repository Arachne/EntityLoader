<?php

namespace ArachneTests\EntityLoader;

class TestPresenter extends \Nette\Application\UI\Presenter
{

	/**
	 * @persistent
	 * @Entity persistent
	 */
	public $persistent;

	final public function __construct()
	{
		throw new \Exception('This class is there for annotations only.');
	}

	/**
	 * @Entity action.id $actionEntity
	 */
	public function actionTestAction($actionEntity)
	{
	}

	/**
	 * @Entity render.id $renderEntity
	 */
	public function renderTestRender($renderEntity)
	{
	}

	/**
	 * @Entity handle.id $handleEntity
	 */
	public function handleTestHandle($handleEntity)
	{
	}

	/**
	 * @Entity (wrong, annotation)
	 */
	public function actionArrayAnnotation($entity)
	{
	}

	/**
	 * @Entity missing dollar
	 */
	public function actionWrongFormat($entity)
	{
	}

	/**
	 * @Entity table $nonexistent
	 */
	public function actionNonexistentPatameter($entity)
	{
	}

	/**
	 * @return \ArachneTests\EntityLoader\TestComponentOne
	 */
	protected function createComponentComponentOne()
	{
	}

	/**
	 * @return \ArachneTests\EntityLoader\TestComponentTwo
	 */
	protected function createComponentComponentTwo()
	{
	}

	/**
	 * @return \ArachneTests\EntityLoader\NonexistentComponent
	 */
	protected function createComponentNonexistentComponent()
	{
	}

}
