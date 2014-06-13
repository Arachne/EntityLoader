<?php

namespace Tests\Unit;

use Arachne\EntityLoader\ParameterFinder;
use Codeception\TestCase\Test;
use Mockery;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Caching\IStorage;
use Tests\Unit\Classes\TestPresenter;

/**
 * @author Jáchym Toušek
 */
class ParameterFinderExceptionsTest extends Test
{

	/** @var ParameterFinder */
	private $finder;

	protected function _before()
	{
		$presenterFactory = Mockery::mock(IPresenterFactory::class);
		$presenterFactory->shouldReceive('getPresenterClass')
			->once()
			->andReturn(TestPresenter::class);
		$storage = Mockery::mock(IStorage::class);
		$storage->shouldReceive('read')
			->once();
		$storage->shouldReceive('lock')
			->once();
		$this->finder = new ParameterFinder($presenterFactory, $storage);
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\ClassNotFoundException
	 * @expectedExceptionMessage Class 'Tests\Unit\Classes\NonexistentComponent' from Tests\Unit\Classes\TestPresenter::createComponentNonexistentComponent @return annotation not found.
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
