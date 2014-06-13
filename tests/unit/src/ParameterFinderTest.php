<?php

namespace Tests\Unit;

use Arachne\EntityLoader\ParameterFinder;
use Codeception\TestCase\Test;
use Mockery;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Caching\IStorage;
use Tests\Unit\Classes\TestPresenter;

/**
 * @author Jáchym Toušek
 */
class ParameterFinderTest extends Test
{

	/** @var ParameterFinder */
	private $finder;

	protected function _before()
	{
		$presenterFactory = Mockery::mock(IPresenterFactory::class);
		$presenterFactory->shouldReceive('getPresenterClass')
			->once()
			->andReturn(TestPresenter::class);
		$storage = Mockery::mock(IStorage::class);
		$storage->shouldReceive('read')
			->once();
		$storage->shouldReceive('lock')
			->once();
		$storage->shouldReceive('write')
			->once();
		$this->finder = new ParameterFinder($presenterFactory, $storage);
	}

	public function testAction()
	{
		$request = new Request('', 'GET', [
			'action' => 'testAction',
			'persistent' => 0,
		]);
		$this->assertEquals([
			'persistent1' => 'Tests\Unit\Classes\Class1',
			'actionEntity' => 'Tests\Unit\Classes\Class2',
		], $this->finder->getEntityParameters($request));
	}

	public function testRenderAndHandle()
	{
		$request = new Request('', 'GET', [
			'action' => 'testRender',
			'do' => 'testHandle',
		]);
		$this->assertEquals([
			'persistent1' => 'Tests\Unit\Classes\Class1',
			'renderEntity' => 'Tests\Unit\Classes\Class3',
			'handleEntity' => 'Tests\Unit\Classes\Class4',
		], $this->finder->getEntityParameters($request));
	}

	public function testComponent()
	{
		$request = new Request('', 'GET', [
			'action' => 'testAction',
			'do' => 'component-testHandle',
			'component-persistent' => 1,
		]);
		$this->assertEquals([
			'persistent1' => 'Tests\Unit\Classes\Class1',
			'actionEntity' => 'Tests\Unit\Classes\Class2',
			'component-persistent' => 'Tests\Unit\Classes\Class5',
			'component-handleEntity' => 'Tests\Unit\Classes\Class6',
		], $this->finder->getEntityParameters($request));
	}

}
