<?php

namespace Tests\Unit;

use Arachne\EntityLoader\Application\ParameterFinder;
use Codeception\MockeryModule\Test;
use Mockery;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Caching\Cache;
use Oops\CacheFactory\Caching\CacheFactory;
use Tests\Unit\Classes\TestPresenter;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
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

		$cache = Mockery::mock(Cache::class);
		$cache->shouldReceive('load')
			->once()
			->with(Mockery::any(), Mockery::type('callable'))
			->andReturnUsing(function ($key, $callback) {
				return $callback($dependencies);
			});

		$cacheFactory = Mockery::mock(CacheFactory::class);
		$cacheFactory->shouldReceive('create')
			->once()
			->andReturn($cache);

		$this->finder = new ParameterFinder($presenterFactory, $cacheFactory);
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\TypeHintException
	 * @expectedExceptionMessage Class 'Tests\Unit\Classes\NonexistentComponent' from Tests\Unit\Classes\TestPresenter::createComponentNonexistentComponent @return annotation not found.
	 */
	public function testNonexistentComponent()
	{
		$request = new Request('', 'GET', [
			'action' => 'testAction',
			'nonexistentComponent-persistent' => 1,
		]);
		$this->finder->getMapping($request);
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\TypeHintException
	 * @expectedExceptionMessage No @return annotation found for method Tests\Unit\Classes\TestPresenter::createComponentMissingTypehint().
	 */
	public function testMissingTypehint()
	{
		$request = new Request('', 'GET', [
			'action' => 'testAction',
			'missingTypehint-persistent' => 1,
		]);
		$this->finder->getMapping($request);
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\TypeHintException
	 * @expectedExceptionMessage Type hint '$invalid' is not valid. Only alphanumeric characters, '_' and '\' are allowed.
	 */
	public function testInvalidTypehint()
	{
		$request = new Request('', 'GET', [
			'action' => 'testAction',
			'invalid-persistent' => 1,
		]);
		$this->finder->getMapping($request);
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\TypeHintException
	 * @expectedExceptionMessage Annotation '@param $invalid' is not valid. The correct format is '@param type $name'. Only alphanumeric characters, '_' and '\' are allowed for the type hint.
	 */
	public function testInvalidTypehintHandle()
	{
		$request = new Request('', 'GET', [
			'action' => 'testAction',
			'do' => 'invalidTypehintHandle',
		]);
		$this->finder->getMapping($request);
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\TypeHintException
	 * @expectedExceptionMessage No type hint found for $handleEntity in Tests\Unit\Classes\TestPresenter::handleMissingTypehintHandle(). Specify it or use '@param mixed $handleEntity' to allow any type.
	 */
	public function testMissingTypehintHandle()
	{
		$request = new Request('', 'GET', [
			'action' => 'testAction',
			'do' => 'missingTypehintHandle',
		]);
		$this->finder->getMapping($request);
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\TypeHintException
	 * @expectedExceptionMessage No type hint found for $handleEntity in Tests\Unit\Classes\TestPresenter::handleNoTypehintHandle(). Specify it or use '@param mixed $handleEntity' to allow any type.
	 */
	public function testNoTypehintHandle()
	{
		$request = new Request('', 'GET', [
			'action' => 'testAction',
			'do' => 'noTypehintHandle',
		]);
		$this->finder->getMapping($request);
	}

}
