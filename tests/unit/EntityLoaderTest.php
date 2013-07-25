<?php

namespace Tests\Arachne\EntityLoader;

use Mockery;

class EntityLoaderTest extends BaseTest
{

	/** @var \Nette\Application\Request */
	private $request;

	/** @var \Arachne\EntityLoader\EntityLoader */
	private $entityLoader;

	/** @var \Mockery\MockInterface */
	private $converter;

	protected function _before()
	{
		$this->request = new \Nette\Application\Request('', 'GET', [
			'non-entity' => 0,
			'entity' => 'value1',
			'component-entity' => 'value2',
		]);
		$finder = Mockery::mock('Arachne\EntityLoader\ParameterFinder');
		$finder->shouldReceive('getEntityParameters')
				->once()
				->andReturn([
					'entity' => 'mapping',
					'component-entity' => 'mapping',
				]);
		$this->converter = Mockery::mock('Arachne\EntityLoader\IConverter');
		$this->entityLoader = new \Arachne\EntityLoader\EntityLoader($finder, $this->converter);
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
		$this->assertParameters();
	}

	public function testRemoveEntities()
	{
		$this->converter
				->shouldReceive('entityToParameter')
				->twice()
				->andReturn(1, 2);
		$this->assertTrue($this->entityLoader->removeEntities($this->request));
		$this->assertParameters();
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
