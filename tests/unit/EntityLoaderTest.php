<?php

namespace ArachneTests\EntityLoader;

use Mockery;

class EntityLoaderTest extends \Codeception\TestCase\Test
{

	/** @var \CodeGuy */
	protected $codeGuy;

	/** @var \Nette\Application\Request */
	private $request;

	/** @var \Arachne\EntityLoader\EntityLoader */
	private $entityLoader;

	/** @var \Arachne\EntityLoader\IParameterConverter */
	private $converter;

	protected function _before()
	{
		$this->request = new \Nette\Application\Request('', 'GET', [
			'non-entity' => 0,
			'entity' => 'value1',
			'component-entity' => 'value2',
		]);
		$this->converter = Mockery::mock('Arachne\EntityLoader\IParameterConverter');
		$finder = Mockery::mock('Arachne\EntityLoader\IParameterFinder')
				->shouldReceive('getEntityParameters')
				->once()
				->andReturn([
					'entity' => 'mapping',
					'component-entity' => 'mapping',
				])
				->getMock();
		$this->entityLoader = new \Arachne\EntityLoader\EntityLoader($finder, $this->converter);
	}

	protected function _after()
	{
		Mockery::close();
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
		$this->converter->shouldReceive('parameterToEntity')
				->twice()
				->andReturn(1, 2);
		$this->assertTrue($this->entityLoader->loadEntities($this->request));
		$this->assertParameters();
	}

	public function testRemoveEntities()
	{
		$this->converter->shouldReceive('entityToParameter')
				->twice()
				->andReturn(1, 2);
		$this->assertTrue($this->entityLoader->removeEntities($this->request));
		$this->assertParameters();
	}

	public function testLoadEntitiesFail()
	{
		$this->converter->shouldReceive('parameterToEntity')
			->twice()
			->andReturn(1, NULL);
		$this->assertFalse($this->entityLoader->loadEntities($this->request));
	}

	public function testRemoveEntitiesFail()
	{
		$this->converter->shouldReceive('entityToParameter')
			->twice()
			->andReturn(1, NULL);
		$this->assertFalse($this->entityLoader->removeEntities($this->request));
	}

}
