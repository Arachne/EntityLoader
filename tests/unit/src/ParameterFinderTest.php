<?php

declare(strict_types=1);

namespace Tests\Unit;

use Arachne\EntityLoader\Application\ParameterFinder;
use Codeception\Test\Unit;
use Eloquent\Phony\Phpunit\Phony;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Caching\Cache;
use Oops\CacheFactory\Caching\CacheFactory;
use StdClass;
use Tests\Unit\Classes\TestPresenter;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ParameterFinderTest extends Unit
{
    /**
     * @var ParameterFinder
     */
    private $finder;

    protected function _before()
    {
        $presenterFactoryHandle = Phony::mock(IPresenterFactory::class);
        $presenterFactoryHandle
            ->getPresenterClass
            ->returns(TestPresenter::class);

        $cacheHandle = Phony::mock(Cache::class);
        $cacheHandle
            ->load
            ->does(
                function ($key, callable $callback) {
                    $dependencies = null;

                    return $callback($dependencies);
                }
            );

        $cacheFactoryHandle = Phony::mock(CacheFactory::class);
        $cacheFactoryHandle
            ->create
            ->returns($cacheHandle->get());

        $this->finder = new ParameterFinder($presenterFactoryHandle->get(), $cacheFactoryHandle->get());
    }

    public function testAction()
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                'persistent' => 0,
            ]
        );

        $this->assertEquals(
            [
                'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
                'actionEntity' => $this->createInfoObject('Tests\Unit\Classes\Class2', false),
                'persistent2' => $this->createInfoObject('string', true),
            ],
            $this->finder->getMapping($request)
        );
    }

    public function testNoAction()
    {
        $request = $this->createRequest(
            [
                'persistent' => 0,
            ]
        );

        $this->assertEquals(
            [
                'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
                'persistent2' => $this->createInfoObject('string', true),
            ],
            $this->finder->getMapping($request)
        );
    }

    public function testRenderAndHandle()
    {
        $request = $this->createRequest(
            [
                'action' => 'testRender',
                'do' => 'testHandle',
            ]
        );

        $this->assertEquals(
            [
                'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
                'renderEntity' => $this->createInfoObject('Tests\Unit\Classes\Class3', false),
                'handleEntity' => $this->createInfoObject('Tests\Unit\Classes\Class4', false),
                'persistent2' => $this->createInfoObject('string', true),
            ],
            $this->finder->getMapping($request)
        );
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
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                'do' => 'component-testHandle',
                'component-persistent' => 1,
            ]
        );

        $this->assertEquals(
            [
                'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
                'actionEntity' => $this->createInfoObject('Tests\Unit\Classes\Class2', false),
                'component-persistent' => $this->createInfoObject('Tests\Unit\Classes\Class5', true),
                'component-handleEntity' => $this->createInfoObject('Tests\Unit\Classes\Class6', false),
                'persistent2' => $this->createInfoObject('string', true),
            ],
            $this->finder->getMapping($request)
        );
    }

    public function testSubComponent()
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                'do' => 'component-sub-testHandle',
                'component-sub-persistent' => 1,
            ]
        );

        $this->assertEquals(
            [
                'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
                'actionEntity' => $this->createInfoObject('Tests\Unit\Classes\Class2', false),
                'component-sub-persistent' => $this->createInfoObject('Tests\Unit\Classes\Class5', true),
                'component-sub-handleEntity' => $this->createInfoObject('Tests\Unit\Classes\Class6', false),
                'persistent2' => $this->createInfoObject('string', true),
            ],
            $this->finder->getMapping($request)
        );
    }

    public function testNamelessComponent()
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                '-persistent' => 1,
            ]
        );

        $this->assertEquals(
            [
                'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
                'actionEntity' => $this->createInfoObject('Tests\Unit\Classes\Class2', false),
                'persistent2' => $this->createInfoObject('string', true),
            ],
            $this->finder->getMapping($request)
        );
    }

    /**
     * @expectedException \Arachne\EntityLoader\Exception\TypeHintException
     * @expectedExceptionMessage Class 'Tests\Unit\Classes\NonexistentComponent' from Tests\Unit\Classes\TestPresenter::createComponentNonexistentComponent return type not found.
     */
    public function testNonexistentComponent()
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                'nonexistentComponent-persistent' => 1,
            ]
        );

        $this->finder->getMapping($request);
    }

    /**
     * @expectedException \Arachne\EntityLoader\Exception\TypeHintException
     * @expectedExceptionMessage Method Tests\Unit\Classes\TestPresenter::createComponentMissingTypehint has no return type.
     */
    public function testMissingTypehint()
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                'missingTypehint-persistent' => 1,
            ]
        );

        $this->finder->getMapping($request);
    }

    /**
     * @expectedException \Arachne\EntityLoader\Exception\TypeHintException
     * @expectedExceptionMessage Method Tests\Unit\Classes\TestPresenter::createComponentBuiltinTypehint does not return a class.
     */
    public function testBuiltinTypehint()
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                'builtinTypehint-persistent' => 1,
            ]
        );

        $this->finder->getMapping($request);
    }

    /**
     * @return Request
     */
    private function createRequest(array $parameters)
    {
        return new Request('', 'GET', $parameters);
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
