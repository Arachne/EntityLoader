<?php

namespace Tests\Unit;

use Arachne\EntityLoader\Application\ParameterFinder;
use Codeception\TestCase\Test;
use Mockery;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Caching\IStorage;
use StdClass;
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
			'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', TRUE),
			'actionEntity' => $this->createInfoObject('Tests\Unit\Classes\Class2', FALSE),
			'persistent2' => $this->createInfoObject('string', TRUE),
		], $this->finder->getMapping($request));
	}

	public function testRenderAndHandle()
	{
		$request = new Request('', 'GET', [
			'action' => 'testRender',
			'do' => 'testHandle',
		]);
		$this->assertEquals([
			'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', TRUE),
			'renderEntity' => $this->createInfoObject('Tests\Unit\Classes\Class3', FALSE),
			'handleEntity' => $this->createInfoObject('Tests\Unit\Classes\Class4', FALSE),
			'persistent2' => $this->createInfoObject('string', TRUE),
		], $this->finder->getMapping($request));
	}

	public function testComponent()
	{
		$request = new Request('', 'GET', [
			'action' => 'testAction',
			'do' => 'component-testHandle',
			'component-persistent' => 1,
		]);
		$this->assertEquals([
			'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', TRUE),
			'actionEntity' => $this->createInfoObject('Tests\Unit\Classes\Class2', FALSE),
			'component-persistent' => $this->createInfoObject('Tests\Unit\Classes\Class5', TRUE),
			'component-handleEntity' => $this->createInfoObject('Tests\Unit\Classes\Class6', FALSE),
			'persistent2' => $this->createInfoObject('string', TRUE),
		], $this->finder->getMapping($request));
	}

	/**
	 * @param string $type
	 * @param bool $nullable
	 * @return StdClass
	 */
	private function createInfoObject($type, $nullable)
	{
		$object = new StdClass();
		$object->type = $type;
		$object->nullable = $nullable;
		return $object;
	}

}
