<?php

namespace Tests\Unit;

use Arachne\EntityLoader\EntityLoader;
use Arachne\EntityLoader\IConverter;
use Arachne\EntityLoader\ParameterFinder;
use Codeception\TestCase\Test;
use Mockery;
use Mockery\MockInterface;
use Nette\Application\Request;

/**
 * @author Jáchym Toušek
 */
class EntityLoaderInTest extends Test
{

	/** @var EntityLoader */
	private $entityLoader;

	/** @var MockInterface */
	private $converter;

	protected function _before()
	{
		$finder = Mockery::mock(ParameterFinder::class);
		$finder->shouldReceive('getEntityParameters')
			->once()
			->andReturn([
				'entity' => 'Type1',
				'component-entity' => 'Type2',
			]);
		$this->converter = Mockery::mock(IConverter::class);
		$this->entityLoader = new EntityLoader([ $this->converter ], $finder);
	}

	public function testLoadEntities()
	{
		$request = new Request('', 'GET', [
			'non-entity' => 0,
			'entity' => 'value1',
			'component-entity' => 'value2',
		]);
		$mock1 = Mockery::mock('Type1');
		$mock2 = Mockery::mock('Type2');
		$this->converter
			->shouldReceive('canConvert')
			->twice()
			->andReturn(TRUE);
		$this->converter
			->shouldReceive('parameterToEntity')
			->twice()
			->andReturn($mock1, $mock2);
		$this->entityLoader->loadEntities($request);
		$this->assertEquals([
			'non-entity' => 0,
			'entity' => $mock1,
			'component-entity' => $mock2,
		], $request->getParameters());
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\UnexpectedValueException
	 * @expectedExceptionMessage Converter did not return an instance of 'Type2'.
	 */
	public function testLoadEntitiesFail()
	{
		$request = new Request('', 'GET', [
			'non-entity' => 0,
			'entity' => 'value1',
			'component-entity' => 'value2',
		]);
		$mock1 = Mockery::mock('Type1');
		$this->converter
			->shouldReceive('canConvert')
			->twice()
			->andReturn(TRUE);
		$this->converter
			->shouldReceive('parameterToEntity')
			->twice()
			->andReturn($mock1, NULL);
		$this->entityLoader->loadEntities($request);
	}

	/**
	 * Make sure that the converter is not called at all if the parameter already has the desired type.
	 */
	public function testLoadEntitiesIgnore()
	{
		$mock1 = Mockery::mock('Type1');
		$mock2 = Mockery::mock('Type2');
		$request = new Request('', 'GET', [
			'non-entity' => 0,
			'entity' => 'value1',
			'component-entity' => $mock2,
		]);
		$this->converter
			->shouldReceive('canConvert')
			->once()
			->with('Type1')
			->andReturn(TRUE);
		$this->converter
			->shouldReceive('parameterToEntity')
			->with('Type1', 'value1')
			->once()
			->andReturn($mock1);
		$this->entityLoader->loadEntities($request);
		$this->assertEquals([
			'non-entity' => 0,
			'entity' => $mock1,
			'component-entity' => $mock2,
		], $request->getParameters());
	}

}
