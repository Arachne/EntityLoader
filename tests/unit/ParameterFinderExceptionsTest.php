<?php

namespace Tests\Arachne\EntityLoader;

use Mockery;

class ParameterFinderExceptionsTest extends BaseTest
{

	/** @var \Arachne\EntityLoader\ParameterFinder */
	private $finder;

	protected function _before()
	{
		$reader = new \Doctrine\Common\Annotations\AnnotationReader();
		$reader->addGlobalIgnoredName('persistent');
		$presenterFactory = Mockery::mock('Nette\Application\IPresenterFactory');
		$presenterFactory->shouldReceive('getPresenterClass')
				->once()
				->andReturn('Tests\TestPresenter');
		$storage = Mockery::mock('Nette\Caching\IStorage');
		$storage->shouldReceive('read')
				->once()
				->andReturnNull();
		$storage->shouldReceive('write')
				->never();
		$this->finder = new \Arachne\EntityLoader\ParameterFinder($reader, $presenterFactory, $storage);
	}

	/**
	 * @expectedException \Arachne\EntityLoader\InvalidStateException
	 * @expectedExceptionMessage Annotation @Entity of 'actionNonexistentPatameter' method uses nonexistent parameter '$nonexistent'.
	 */
	public function testNonexistentParameter()
	{
		$request = new \Nette\Application\Request('', 'GET', [
			'action' => 'nonexistentPatameter',
		]);
		$this->finder->getEntityParameters($request);
	}

	/**
	 * @expectedException \Arachne\EntityLoader\InvalidStateException
	 * @expectedExceptionMessage Class 'Tests\NonexistentComponent' from Tests\TestPresenter::createComponentNonexistentComponent @return annotation not found.
	 */
	public function testNonexistentComponent()
	{
		$request = new \Nette\Application\Request('', 'GET', [
			'action' => 'testAction',
			'nonexistentComponent-persistent' => 1,
		]);
		$this->finder->getEntityParameters($request);
	}

}
