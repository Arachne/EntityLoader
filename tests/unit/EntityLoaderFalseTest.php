<?php

namespace ArachneTests\EntityLoader;

use Mockery;

class EntityLoaderFalseTest extends BaseTest
{

	/** @var \Nette\Application\Request */
	private $request;

	/** @var \Arachne\EntityLoader\EntityLoader */
	private $entityLoader;

	/** @var \Arachne\EntityLoader\IParameterConverter */
	private $converter;

	protected function setUp()
	{
		parent::setUp();

		// Create alias mock class before the real class is autoloaded
		$this->request = $request = Mockery::mock('alias:Nette\Application\Request');
		$request->shouldReceive('getParameters')
			->once()
			->andReturn([
				'non-entity' => 0,
				'entity' => 'value1',
				'component-entity' => 'value2',
			]);
		$request->shouldReceive('setParameters')
			->never();

		$this->converter = $converter = Mockery::mock('Arachne\EntityLoader\IParameterConverter');
		$finder = Mockery::mock('Arachne\EntityLoader\IParameterFinder')
			->shouldReceive('getEntityParameters')
			->once()
			->andReturn([
				'entity' => 'mapping',
				'component-entity' => 'mapping',
			])
			->getMock();
		$this->entityLoader = new \Arachne\EntityLoader\EntityLoader($finder, $converter);
	}

	public function testLoadEntities()
	{
		$this->converter->shouldReceive('parameterToEntity')
			->twice()
			->andReturn(1, NULL);
		$this->assertFalse($this->entityLoader->loadEntities($this->request));
	}

	public function testRemoveEntities()
	{
		$this->converter->shouldReceive('entityToParameter')
			->twice()
			->andReturn(1, NULL);
		$this->assertFalse($this->entityLoader->removeEntities($this->request));
	}

}
