<?php

namespace Tests\Unit;

use Arachne\EntityLoader\Application\ParameterFinder;
use Codeception\MockeryModule\Test;
use Mockery;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Caching\Cache;
use Oops\CacheFactory\Caching\CacheFactory;
use StdClass;
use Tests\Unit\Classes\TestPresenter;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ParameterFinderTest extends Test
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

    public function testAction()
    {
        $request = new Request('', 'GET', [
            'action' => 'testAction',
            'persistent' => 0,
        ]);
        $this->assertEquals([
            'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
            'actionEntity' => $this->createInfoObject('Tests\Unit\Classes\Class2', false),
            'persistent2' => $this->createInfoObject('string', true),
        ], $this->finder->getMapping($request));
    }

    public function testNoAction()
    {
        $request = new Request('', 'GET', [
            'persistent' => 0,
        ]);
        $this->assertEquals([
            'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
            'persistent2' => $this->createInfoObject('string', true),
        ], $this->finder->getMapping($request));
    }

    public function testRenderAndHandle()
    {
        $request = new Request('', 'GET', [
            'action' => 'testRender',
            'do' => 'testHandle',
        ]);
        $this->assertEquals([
            'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
            'renderEntity' => $this->createInfoObject('Tests\Unit\Classes\Class3', false),
            'handleEntity' => $this->createInfoObject('Tests\Unit\Classes\Class4', false),
            'persistent2' => $this->createInfoObject('string', true),
        ], $this->finder->getMapping($request));
    }

    public function testNoTypehintHandle()
    {
        $request = new Request('', 'GET', [
            'action' => 'testAction',
            'do' => 'noTypehintHandle',
        ]);
        $this->assertEquals([
            'actionEntity' => $this->createInfoObject('Tests\Unit\Classes\Class2', false),
            'handleEntity' => $this->createInfoObject('mixed', false),
            'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
            'persistent2' => $this->createInfoObject('string', true),
        ], $this->finder->getMapping($request));
    }

    public function testComponent()
    {
        $request = new Request('', 'GET', [
            'action' => 'testAction',
            'do' => 'component-testHandle',
            'component-persistent' => 1,
        ]);
        $this->assertEquals([
            'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
            'actionEntity' => $this->createInfoObject('Tests\Unit\Classes\Class2', false),
            'component-persistent' => $this->createInfoObject('Tests\Unit\Classes\Class5', true),
            'component-handleEntity' => $this->createInfoObject('Tests\Unit\Classes\Class6', false),
            'persistent2' => $this->createInfoObject('string', true),
        ], $this->finder->getMapping($request));
    }

    public function testSubComponent()
    {
        $request = new Request('', 'GET', [
            'action' => 'testAction',
            'do' => 'component-sub-testHandle',
            'component-sub-persistent' => 1,
        ]);
        $this->assertEquals([
            'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
            'actionEntity' => $this->createInfoObject('Tests\Unit\Classes\Class2', false),
            'component-sub-persistent' => $this->createInfoObject('Tests\Unit\Classes\Class5', true),
            'component-sub-handleEntity' => $this->createInfoObject('Tests\Unit\Classes\Class6', false),
            'persistent2' => $this->createInfoObject('string', true),
        ], $this->finder->getMapping($request));
    }

    public function testNamelessComponent()
    {
        $request = new Request('', 'GET', [
            'action' => 'testAction',
            '-persistent' => 1,
        ]);
        $this->assertEquals([
            'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
            'actionEntity' => $this->createInfoObject('Tests\Unit\Classes\Class2', false),
            'persistent2' => $this->createInfoObject('string', true),
        ], $this->finder->getMapping($request));
    }

    /**
     * @param string $type
     * @param bool   $optional
     *
     * @return StdClass
     */
    private function createInfoObject($type, $optional)
    {
        $object = new StdClass();
        $object->type = $type;
        $object->optional = $optional;

        return $object;
    }
}
