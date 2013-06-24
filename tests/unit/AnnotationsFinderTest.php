<?php

namespace Tests\Arachne\EntityLoader;

use Mockery;

final class AnnotationsFinderTest extends \Codeception\TestCase\Test
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
				->once()
				->andReturn();
		$this->finder = new \Arachne\EntityLoader\AnnotationsFinder($presenterFactory, $storage);
	}

	protected function _after()
	{
		Mockery::close();
	}

	public function testAction()
	{
		$request = new \Nette\Application\Request('', 'GET', [
			'action' => 'testAction',
			'persistent' => 0,
		]);
		$this->assertSame([
			'persistent' => 'persistent',
			'actionEntity' => 'action.id',
		], $this->finder->getEntityParameters($request));
	}

	public function testRenderAndHandle()
	{
		$request = new \Nette\Application\Request('', 'GET', [
			'action' => 'testRender',
			'do' => 'testHandle',
		]);
		$this->assertSame([
			'persistent' => 'persistent',
			'renderEntity' => 'render.id',
			'handleEntity' => 'handle.id',
		], $this->finder->getEntityParameters($request));
	}

	public function testComponent()
	{
		$request = new \Nette\Application\Request('', 'GET', [
			'action' => 'testAction',
			'do' => 'componentOne-testHandle',
			'componentOne-persistent' => 1,
		]);
		$this->assertSame([
			'persistent' => 'persistent',
			'actionEntity' => 'action.id',
			'componentOne-persistent' => 'mapping',
			'componentOne-handleEntity' => 'handle',
		], $this->finder->getEntityParameters($request));
	}

}
