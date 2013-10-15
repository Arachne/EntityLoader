<?php

namespace Tests\Unit;

use Arachne\EntityLoader\EntityLoader;
use Arachne\EntityLoader\EntityProxy;
use Mockery;
use Mockery\MockInterface;
use Nette\Application\Request;

class EntityLoaderTest extends BaseTest
{

	/** @var Request */
	private $request;

	/** @var EntityLoader */
	private $entityLoader;

	/** @var MockInterface */
	private $converter;

	/** @var MockInterface */
	private $converterLoader;

	protected function _before()
	{
		$this->request = new Request('', 'GET', [
			'non-entity' => 0,
			'entity' => 'value1',
			'component-entity' => 'value2',
		]);
		$finder = Mockery::mock('Arachne\EntityLoader\ParameterFinder');
		$finder->shouldReceive('getEntityParameters')
			->once()
			->andReturn([
				'entity' => 'Type1',
				'component-entity' => 'Type2',
			]);
		$this->converter = Mockery::mock('Arachne\EntityLoader\IConverter');
		$this->converterLoader = Mockery::mock('Arachne\EntityLoader\IConverterLoader');
		$this->converterLoader->shouldReceive('getConverter')
			->twice()
			->andReturnUsing(function () {
				return $this->converter;
			});
		$this->entityLoader = new EntityLoader($finder, $this->converterLoader);
	}

	private function assertParameters()
	{
		$this->assertEquals([
			'non-entity' => 0,
			'entity' => 1,
			'component-entity' => 2,
		], $this->request->getParameters());
	}

	public function testLoadEntities()
	{
		$this->converter
			->shouldReceive('parameterToEntity')
			->twice()
			->andReturn(1, 2);
		$this->assertTrue($this->entityLoader->loadEntities($this->request));
		$this->assertEquals([
			'non-entity' => 0,
			'entity' => 1,
			'component-entity' => 2,
		], $this->request->getParameters());
	}

	public function testRemoveEntities()
	{
		$this->converter
			->shouldReceive('entityToParameter')
			->twice()
			->andReturn(1, 2);
		$this->assertTrue($this->entityLoader->removeEntities($this->request));
		$this->assertEquals([
			'non-entity' => 0,
			'entity' => new EntityProxy('value1', 1),
			'component-entity' => new EntityProxy('value2', 2),
		], $this->request->getParameters());
	}

	public function testLoadEntitiesFail()
	{
		$this->converter
			->shouldReceive('parameterToEntity')
			->twice()
			->andReturn(1, NULL);
		$this->assertFalse($this->entityLoader->loadEntities($this->request));
	}

	public function testRemoveEntitiesFail()
	{
		$this->converter
			->shouldReceive('entityToParameter')
			->twice()
			->andReturn(1, NULL);
		$this->assertFalse($this->entityLoader->removeEntities($this->request));
	}

}
