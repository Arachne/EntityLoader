<?php

namespace Tests\Arachne\EntityLoader;

use Arachne\EntityLoader\InvalidStateException;
use Arachne\EntityLoader\ParameterFinder;
use Doctrine\Common\Annotations\AnnotationReader;
use Mockery;
use Nette\Application\Request;

class ParameterFinderExceptionsTest extends BaseTest
{

	/** @var ParameterFinder */
	private $finder;

	protected function _before()
	{
		$reader = new AnnotationReader();
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
		$this->finder = new ParameterFinder($reader, $presenterFactory, $storage);
	}

	/**
	 * @expectedException InvalidStateException
	 * @expectedExceptionMessage Annotation @Entity of 'actionNonexistentPatameter' method uses nonexistent parameter '$nonexistent'.
	 */
	public function testNonexistentParameter()
	{
		$request = new Request('', 'GET', [
			'action' => 'nonexistentPatameter',
		]);
		$this->finder->getEntityParameters($request);
	}

	/**
	 * @expectedException InvalidStateException
	 * @expectedExceptionMessage Class 'Tests\NonexistentComponent' from Tests\TestPresenter::createComponentNonexistentComponent @return annotation not found.
	 */
	public function testNonexistentComponent()
	{
		$request = new Request('', 'GET', [
			'action' => 'testAction',
			'nonexistentComponent-persistent' => 1,
		]);
		$this->finder->getEntityParameters($request);
	}

}
