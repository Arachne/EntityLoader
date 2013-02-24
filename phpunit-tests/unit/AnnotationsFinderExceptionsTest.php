<?php

namespace ArachneTests\EntityLoader;

use Mockery;

class AnnotationsFinderExceptionsTest extends BaseTest
{

	/** @var \Arachne\EntityLoader\Finders\AnnotationsFinder */
	private $finder;

	protected function setUp()
	{
		parent::setUp();
		$presenterFactory = Mockery::mock('Nette\Application\PresenterFactory')
				->shouldReceive('getPresenterClass')
				->once()
				->andReturn('ArachneTests\EntityLoader\TestPresenter')
				->getMock();
		$storage = Mockery::mock('Nette\Caching\IStorage');
		$storage->shouldReceive('read')
				->once()
				->andReturnNull();
		$storage->shouldReceive('write')
				->never();
		$this->finder = new \Arachne\EntityLoader\Finders\AnnotationsFinder($presenterFactory, $storage);
	}

	/**
	 * @expectedException \Arachne\EntityLoader\InvalidStateException
	 * @expectedExceptionMessage Annotation @Entity of 'actionNonexistentPatameter' method uses nonexistent parameter '$nonexistent'.
	 */
	public function testWrongAnnotation()
	{
		$request = Mockery::mock('alias:Nette\Application\Request');
		$request->shouldReceive('getParameters')
			->once()
			->andReturn([
				'action' => 'nonexistentPatameter',
			]);
		$request->shouldReceive('getPresenterName')
			->once();
		$this->finder->getEntityParameters($request);
	}

	/**
	 * @expectedException \Arachne\EntityLoader\InvalidStateException
	 * @expectedExceptionMessage Annotation @Entity of 'actionArrayAnnotation' method is not a string.
	 */
	public function testArrayAnnotation()
	{
		$request = Mockery::mock('alias:Nette\Application\Request');
		$request->shouldReceive('getParameters')
			->once()
			->andReturn([
				'action' => 'arrayAnnotation',
			]);
		$request->shouldReceive('getPresenterName')
			->once();
		$this->finder->getEntityParameters($request);
	}

	/**
	 * @expectedException \Arachne\EntityLoader\InvalidStateException
	 * @expectedExceptionMessage Annotation @Entity of 'actionWrongFormat' method doesn't have correct format.
	 */
	public function testWrongFormat()
	{
		$request = Mockery::mock('alias:Nette\Application\Request');
		$request->shouldReceive('getParameters')
			->once()
			->andReturn([
				'action' => 'wrongFormat',
			]);
		$request->shouldReceive('getPresenterName')
			->once();
		$this->finder->getEntityParameters($request);
	}

	/**
	 * @expectedException \Arachne\EntityLoader\InvalidStateException
	 * @expectedExceptionMessage Annotation @Entity of 'actionNonexistentPatameter' method uses nonexistent parameter '$nonexistent'.
	 */
	public function testNonexistentParameter()
	{
		$request = Mockery::mock('alias:Nette\Application\Request');
		$request->shouldReceive('getParameters')
			->once()
			->andReturn([
				'action' => 'nonexistentPatameter',
			]);
		$request->shouldReceive('getPresenterName')
			->once();
		$this->finder->getEntityParameters($request);
	}

	/**
	 * @expectedException \Arachne\EntityLoader\InvalidStateException
	 * @expectedExceptionMessage Annotation @Entity of '$persistent' property is not a string.
	 */
	public function testComponentArrayAnnotation()
	{
		$request = Mockery::mock('alias:Nette\Application\Request');
		$request->shouldReceive('getParameters')
			->once()
			->andReturn([
				'action' => 'testAction',
				'componentTwo-persistent' => 1,
			]);
		$request->shouldReceive('getPresenterName')
			->once();
		$this->finder->getEntityParameters($request);
	}

	/**
	 * @expectedException \Arachne\EntityLoader\InvalidStateException
	 * @expectedExceptionMessage Class '\ArachneTests\EntityLoader\NonexistentComponent' from ArachneTests\EntityLoader\TestPresenter::createComponentNonexistentComponent @return annotation not found.
	 */
	public function testNonexistentComponent()
	{
		$request = Mockery::mock('alias:Nette\Application\Request');
		$request->shouldReceive('getParameters')
			->once()
			->andReturn([
				'action' => 'testAction',
				'nonexistentComponent-persistent' => 1,
			]);
		$request->shouldReceive('getPresenterName')
			->once();
		$this->finder->getEntityParameters($request);
	}

}
