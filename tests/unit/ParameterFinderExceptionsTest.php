<?php

namespace Tests\Unit;

use Arachne\EntityLoader\ParameterFinder;
use Mockery;
use Nette\Application\Request;

class ParameterFinderExceptionsTest extends BaseTest
{

	/** @var ParameterFinder */
	private $finder;

	protected function _before()
	{
		$presenterFactory = Mockery::mock('Nette\Application\IPresenterFactory');
		$presenterFactory->shouldReceive('getPresenterClass')
			->once()
			->andReturn('Tests\Unit\TestPresenter');
		$storage = Mockery::mock('Nette\Caching\IStorage');
		$storage->shouldReceive('read')
			->once();
		$storage->shouldReceive('lock')
			->once();
		$storage->shouldReceive('write')
			->never();
		$this->finder = new ParameterFinder($presenterFactory, $storage);
	}

	/**
	 * @expectedException Arachne\EntityLoader\InvalidStateException
	 * @expectedExceptionMessage Class 'Tests\Unit\NonexistentComponent' from Tests\Unit\TestPresenter::createComponentNonexistentComponent @return annotation not found.
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
