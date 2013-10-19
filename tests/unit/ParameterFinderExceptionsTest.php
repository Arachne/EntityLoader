<?php

namespace Tests\Unit;

use Arachne\EntityLoader\ParameterFinder;
use Codeception\TestCase\Test;
use Mockery;
use Nette\Application\Request;

/**
 * @author Jáchym Toušek
 */
class ParameterFinderExceptionsTest extends Test
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
		$this->finder = new ParameterFinder($presenterFactory, $storage);
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\ClassNotFoundException
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
