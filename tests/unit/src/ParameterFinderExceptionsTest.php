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
    /**
     * @var ParameterFinder
     */
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
     * @expectedExceptionMessage Class 'Tests\Unit\Classes\NonexistentComponent' from Tests\Unit\Classes\TestPresenter::createComponentNonexistentComponent return type not found.
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
     * @expectedExceptionMessage Method Tests\Unit\Classes\TestPresenter::createComponentMissingTypehint has no return type.
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
     * @expectedExceptionMessage Method Tests\Unit\Classes\TestPresenter::createComponentBuiltinTypehint does not return a class.
     */
    public function testBuiltinTypehint()
    {
        $request = new Request('', 'GET', [
            'action' => 'testAction',
            'builtinTypehint-persistent' => 1,
        ]);
        $this->finder->getMapping($request);
    }
}
