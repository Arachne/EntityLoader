<?php

namespace Tests\Arachne\EntityLoader;

use Mockery;

class ParameterFinderTest extends BaseTest
{

	/** @var \Arachne\EntityLoader\ParameterFinder */
	private $finder;

	protected function _before()
	{
		$reader = new \Doctrine\Common\Annotations\AnnotationReader();
		$reader->addGlobalIgnoredName('persistent');
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
		$this->finder = new \Arachne\EntityLoader\ParameterFinder($reader, $presenterFactory, $storage);
	}

	public function testAction()
	{
		$request = new \Nette\Application\Request('', 'GET', [
			'action' => 'testAction',
			'persistent' => 0,
		]);
		$this->assertEquals([
			'persistent' => $this->createEntity(NULL, 'persistent', 'id'),
			'actionEntity' => $this->createEntity('actionEntity', 'action', 'id'),
		], $this->finder->getEntityParameters($request));
	}

	public function testRenderAndHandle()
	{
		$request = new \Nette\Application\Request('', 'GET', [
			'action' => 'testRender',
			'do' => 'testHandle',
		]);
		$this->assertEquals([
			'persistent' => $this->createEntity(NULL, 'persistent', 'id'),
			'renderEntity' => $this->createEntity('renderEntity', 'render', 'id'),
			'handleEntity' => $this->createEntity('handleEntity', 'handle', 'id'),
		], $this->finder->getEntityParameters($request));
	}

	public function testComponent()
	{
		$request = new \Nette\Application\Request('', 'GET', [
			'action' => 'testAction',
			'do' => 'component-testHandle',
			'component-persistent' => 1,
		]);
		$this->assertEquals([
			'persistent' => $this->createEntity(NULL, 'persistent', 'id'),
			'actionEntity' => $this->createEntity('actionEntity', 'action', 'id'),
			'component-persistent' => $this->createEntity(NULL, 'mapping', 'id'),
			'component-handleEntity' => $this->createEntity('handleEntity', 'handle', 'id'),
		], $this->finder->getEntityParameters($request));
	}

	/**
	 * @param string $parameter
	 * @param string $class
	 * @param string $property
	 * @return \Arachne\EntityLoader\Entity
	 */
	private function createEntity($parameter, $class, $property)
	{
		$entity = new \Arachne\EntityLoader\Entity();
		$entity->parameter = $parameter;
		$entity->entity = $class;
		$entity->property = $property;
		return $entity;
	}

}
