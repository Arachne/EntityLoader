<?php

namespace Tests\Unit;

use Arachne\EntityLoader\EntityLoader;
use Arachne\EntityLoader\EntityEnvelope;
use Arachne\EntityLoader\IConverter;
use Codeception\TestCase\Test;
use Mockery;
use Mockery\MockInterface;

/**
 * @author Jáchym Toušek
 */
class EntityLoaderOutTest extends Test
{

	/** @var EntityLoader */
	private $entityLoader;

	/** @var MockInterface */
	private $converter;

	/** @var string[] */
	private $mapping;

	protected function _before()
	{
		$this->mapping = [
			'entity' => 'Type1',
			'component-entity' => 'Type2',
		];
		$this->converter = Mockery::mock(IConverter::class);
		$this->entityLoader = new EntityLoader([ $this->converter ]);
	}

	public function testRemoveEntities()
	{
		$parameters = [
			'non-entity' => 0,
			'entity' => Mockery::mock('Type1'),
			'component-entity' => Mockery::mock('Type2'),
		];
		$this->converter
			->shouldReceive('canConvert')
			->twice()
			->andReturn(TRUE);
		$this->converter
			->shouldReceive('entityToParameter')
			->twice()
			->andReturn('1', '2');
		$this->assertEquals([
			'non-entity' => 0,
			'entity' => 1,
			'component-entity' => 2,
		], $this->entityLoader->removeEntities($parameters, $this->mapping));
	}

	public function testRemoveEntitiesProxies()
	{
		$mock1 = Mockery::mock('Type1');
		$mock2 = Mockery::mock('Type2');
		$parameters = [
			'non-entity' => 0,
			'entity' => $mock1,
			'component-entity' => $mock2,
		];
		$this->converter
			->shouldReceive('canConvert')
			->twice()
			->andReturn(TRUE);
		$this->converter
			->shouldReceive('entityToParameter')
			->twice()
			->andReturn('1', '2');
		$this->assertEquals([
			'non-entity' => 0,
			'entity' => new EntityEnvelope($mock1, 1),
			'component-entity' => new EntityEnvelope($mock2, 2),
		], $this->entityLoader->removeEntities($parameters, $this->mapping, TRUE));
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\UnexpectedValueException
	 * @expectedExceptionMessage Converter for 'Type2' did not return a string.
	 */
	public function testRemoveEntitiesFail()
	{
		$parameters = [
			'non-entity' => 0,
			'entity' => Mockery::mock('Type1'),
			'component-entity' => Mockery::mock('Type2'),
		];
		$this->converter
			->shouldReceive('canConvert')
			->twice()
			->andReturn(TRUE);
		$this->converter
			->shouldReceive('entityToParameter')
			->twice()
			->andReturn('1', NULL);
		$this->entityLoader->removeEntities($parameters, $this->mapping);
	}

	public function testRemoveEntitiesIgnore()
	{
		$mock1 = Mockery::mock('Type1');
		$parameters = [
			'non-entity' => 0,
			'entity' => $mock1,
			'component-entity' => 'value2',
		];
		$this->converter
			->shouldReceive('canConvert')
			->once()
			->with('Type1')
			->andReturn(TRUE);
		$this->converter
			->shouldReceive('entityToParameter')
			->once()
			->with('Type1', $mock1)
			->andReturn('1');
		$this->assertEquals([
			'non-entity' => 0,
			'entity' => 1,
			'component-entity' => 'value2',
		], $this->entityLoader->removeEntities($parameters, $this->mapping));
	}

}
