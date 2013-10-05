<?php

namespace Tests\Arachne\EntityLoader;

use Arachne\EntityLoader\Entity;
use Arachne\EntityLoader\ParameterFinder;
use Doctrine\Common\Annotations\AnnotationReader;
use Mockery;
use Nette\Application\Request;

class ParameterFinderTest extends BaseTest
{

	/** @var ParameterFinder */
	private $finder;

	protected function _before()
	{
		$reader = new AnnotationReader();
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
		$this->finder = new ParameterFinder($reader, $presenterFactory, $storage);
	}

	public function testAction()
	{
		$request = new Request('', 'GET', [
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
		$request = new Request('', 'GET', [
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
		$request = new Request('', 'GET', [
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
	 * @return Entity
	 */
	private function createEntity($parameter, $class, $property)
	{
		$entity = new Entity();
		$entity->parameter = $parameter;
		$entity->entity = $class;
		$entity->property = $property;
		return $entity;
	}

}
