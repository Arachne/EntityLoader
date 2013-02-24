<?php

namespace ArachneTests\EntityLoader;

use Mockery;

class AnnotationsFinderTest extends BaseTest
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
				->once()
				->andReturn();
		$this->finder = new \Arachne\EntityLoader\Finders\AnnotationsFinder($presenterFactory, $storage);
	}

	public function testAction()
	{
		$request = Mockery::mock('alias:Nette\Application\Request');
		$request->shouldReceive('getParameters')
			->once()
			->andReturn([
				'action' => 'testAction',
				'persistent' => 0,
			]);
		$request->shouldReceive('getPresenterName')
			->once();
		$this->assertSame([
			'persistent' => 'persistent',
			'actionEntity' => 'action.id',
		], $this->finder->getEntityParameters($request));
	}

	public function testRenderAndHandle()
	{
		$request = Mockery::mock('alias:Nette\Application\Request');
		$request->shouldReceive('getParameters')
			->once()
			->andReturn([
				'action' => 'testRender',
				'do' => 'testHandle',
			]);
		$request->shouldReceive('getPresenterName')
			->once();
		$this->assertSame([
			'persistent' => 'persistent',
			'renderEntity' => 'render.id',
			'handleEntity' => 'handle.id',
		], $this->finder->getEntityParameters($request));
	}

	public function testComponent()
	{
		$request = Mockery::mock('alias:Nette\Application\Request');
		$request->shouldReceive('getParameters')
			->once()
			->andReturn([
				'action' => 'testAction',
				'do' => 'componentOne-testHandle',
				'componentOne-persistent' => 1,
			]);
		$request->shouldReceive('getPresenterName')
			->once();
		$this->assertSame([
			'persistent' => 'persistent',
			'actionEntity' => 'action.id',
			'componentOne-persistent' => 'mapping',
			'componentOne-handleEntity' => 'handle',
		], $this->finder->getEntityParameters($request));
	}

}
