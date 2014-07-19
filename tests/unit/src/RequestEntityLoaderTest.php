<?php

namespace Tests\Unit;

use Arachne\EntityLoader\Application\ParameterFinder;
use Arachne\EntityLoader\Application\RequestEntityLoader;
use Arachne\EntityLoader\EntityLoader;
use Codeception\TestCase\Test;
use Mockery;
use Mockery\MockInterface;
use Nette\Application\Request;

/**
 * @author Jáchym Toušek
 */
class RequestEntityLoaderTest extends Test
{

	/** @var RequestEntityLoader */
	private $requestEntityLoader;

	/** @var MockInterface */
	private $entityLoader;

	/** @var MockInterface */
	private $finder;

	protected function _before()
	{
		$this->finder = Mockery::mock(ParameterFinder::class);
		$this->entityLoader = Mockery::mock(EntityLoader::class);
		$this->requestEntityLoader = new RequestEntityLoader($this->entityLoader, $this->finder);
	}

	public function testLoadEntities()
	{
		$expected = [
			'entity' => 'value2',
		];
		$request = new Request('', 'GET', [
			'entity' => 'value1',
		]);
		$mapping = [
			'entity' => 'Type1',
		];
		$this->finder->shouldReceive('getMapping')
			->once()
			->with($request)
			->andReturn($mapping);
		$this->entityLoader
			->shouldReceive('loadEntities')
			->once()
			->with($request->getParameters(), $mapping)
			->andReturn($expected);
		$this->requestEntityLoader->loadEntities($request);
		$this->assertEquals($expected, $request->getParameters());
	}

	public function testLoadEntitiesEmptyMapping()
	{
		$expected = [
			'entity' => 'value1',
		];
		$request = new Request('', 'GET', $expected);
		$mapping = [];
		$this->finder->shouldReceive('getMapping')
			->once()
			->with($request)
			->andReturn($mapping);
		$this->requestEntityLoader->loadEntities($request);
		$this->assertEquals($expected, $request->getParameters());
	}

	public function testRemoveEntities()
	{
		$expected = [
			'entity' => 'value2',
		];
		$request = new Request('', 'GET', [
			'entity' => 'value1',
		]);
		$mapping = [
			'entity' => 'Type1',
		];
		$this->finder->shouldReceive('getMapping')
			->once()
			->with($request)
			->andReturn($mapping);
		$this->entityLoader
			->shouldReceive('removeEntities')
			->once()
			->with($request->getParameters(), $mapping, FALSE)
			->andReturn($expected);
		$this->requestEntityLoader->removeEntities($request);
		$this->assertEquals($expected, $request->getParameters());
	}

	public function testRemoveEntitiesEmptyMapping()
	{
		$expected = [
			'entity' => 'value1',
		];
		$request = new Request('', 'GET', $expected);
		$mapping = [];
		$this->finder->shouldReceive('getMapping')
			->once()
			->with($request)
			->andReturn($mapping);
		$this->requestEntityLoader->removeEntities($request);
		$this->assertEquals($expected, $request->getParameters());
	}

	public function testRemoveEntitiesEnvelopes()
	{
		$expected = [
			'entity' => 'value2',
		];
		$request = new Request('', 'GET', [
			'entity' => 'value1',
		]);
		$mapping = [
			'entity' => 'Type1',
		];
		$this->finder->shouldReceive('getMapping')
			->once()
			->with($request)
			->andReturn($mapping);
		$this->entityLoader
			->shouldReceive('removeEntities')
			->once()
			->with($request->getParameters(), $mapping, TRUE)
			->andReturn($expected);
		$this->requestEntityLoader->removeEntities($request, TRUE);
		$this->assertEquals($expected, $request->getParameters());
	}

}
