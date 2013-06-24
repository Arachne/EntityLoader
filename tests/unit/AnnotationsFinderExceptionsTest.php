<?php

namespace Tests\Arachne\EntityLoader;

use Mockery;

final class AnnotationsFinderExceptionsTest extends \Codeception\TestCase\Test
{

	/** @var \Arachne\EntityLoader\AnnotationsFinder */
	private $finder;

	protected function _before()
	{
		$presenterFactory = Mockery::mock('Nette\Application\IPresenterFactory')
				->shouldReceive('getPresenterClass')
				->once()
				->andReturn('Tests\TestPresenter')
				->getMock();
		$storage = Mockery::mock('Nette\Caching\IStorage');
		$storage->shouldReceive('read')
				->once()
				->andReturnNull();
		$storage->shouldReceive('write')
				->never();
		$this->finder = new \Arachne\EntityLoader\AnnotationsFinder($presenterFactory, $storage);
	}

	protected function _after()
	{
		Mockery::close();
	}

	/**
	 * @expectedException \Arachne\EntityLoader\InvalidStateException
	 * @expectedExceptionMessage Annotation @Entity of 'actionNonexistentPatameter' method uses nonexistent parameter '$nonexistent'.
	 */
	public function testWrongAnnotation()
	{
		$request = new \Nette\Application\Request('', 'GET', [
			'action' => 'nonexistentPatameter',
		]);
		$this->finder->getEntityParameters($request);
	}

	/**
	 * @expectedException \Arachne\EntityLoader\InvalidStateException
	 * @expectedExceptionMessage Annotation @Entity of 'actionArrayAnnotation' method is not a string.
	 */
	public function testArrayAnnotation()
	{
		$request = new \Nette\Application\Request('', 'GET', [
			'action' => 'arrayAnnotation',
		]);
		$this->finder->getEntityParameters($request);
	}

	/**
	 * @expectedException \Arachne\EntityLoader\InvalidStateException
	 * @expectedExceptionMessage Annotation @Entity of 'actionWrongFormat' method doesn't have correct format.
	 */
	public function testWrongFormat()
	{
		$request = new \Nette\Application\Request('', 'GET', [
			'action' => 'wrongFormat',
		]);
		$this->finder->getEntityParameters($request);
	}

	/**
	 * @expectedException \Arachne\EntityLoader\InvalidStateException
	 * @expectedExceptionMessage Annotation @Entity of 'actionNonexistentPatameter' method uses nonexistent parameter '$nonexistent'.
	 */
	public function testNonexistentParameter()
	{
		$request = new \Nette\Application\Request('', 'GET', [
			'action' => 'nonexistentPatameter',
		]);
		$this->finder->getEntityParameters($request);
	}

	/**
	 * @expectedException \Arachne\EntityLoader\InvalidStateException
	 * @expectedExceptionMessage Annotation @Entity of '$persistent' property is not a string.
	 */
	public function testComponentArrayAnnotation()
	{
		$request = new \Nette\Application\Request('', 'GET', [
			'action' => 'testAction',
			'componentTwo-persistent' => 1,
		]);
		$this->finder->getEntityParameters($request);
	}

	/**
	 * @expectedException \Arachne\EntityLoader\InvalidStateException
	 * @expectedExceptionMessage Class 'Tests\NonexistentComponent' from Tests\TestPresenter::createComponentNonexistentComponent @return annotation not found.
	 */
	public function testNonexistentComponent()
	{
		$request = new \Nette\Application\Request('', 'GET', [
			'action' => 'testAction',
			'nonexistentComponent-persistent' => 1,
		]);
		$this->finder->getEntityParameters($request);
	}

}