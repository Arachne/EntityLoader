<?php

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
     * @expectedExceptionMessage Class 'Tests\Unit\Classes\NonexistentComponent' from Tests\Unit\Classes\TestPresenter::createComponentNonexistentComponent @return annotation not found.
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
     * @expectedExceptionMessage No @return annotation found for method Tests\Unit\Classes\TestPresenter::createComponentMissingTypehint().
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
     * @expectedExceptionMessage Annotation '@param $invalid' is not valid. The correct format is '@param type $name'. Only alphanumeric characters, '_' and '\' are allowed for the type hint.
     */
    public function testInvalidTypehintHandle()
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                'do' => 'invalidTypehintHandle',
            ]
        );

        $this->finder->getMapping($request);
    }

    /**
     * @expectedException \Arachne\EntityLoader\Exception\TypeHintException
     * @expectedExceptionMessage No type hint found for $handleEntity in Tests\Unit\Classes\TestPresenter::handleMissingTypehintHandle(). Specify it or use '@param mixed $handleEntity' to allow any type.
     */
    public function testMissingTypehintHandle()
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                'do' => 'missingTypehintHandle',
            ]
        );

        $this->finder->getMapping($request);
    }

    /**
     * @expectedException \Arachne\EntityLoader\Exception\TypeHintException
     * @expectedExceptionMessage No type hint found for $handleEntity in Tests\Unit\Classes\TestPresenter::handleNoTypehintHandle(). Specify it or use '@param mixed $handleEntity' to allow any type.
     */
    public function testNoTypehintHandle()
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                'do' => 'noTypehintHandle',
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
     * @param bool   $nullable
     *
     * @return StdClass
     */
    private function createInfoObject($type, $nullable)
    {
        $object = new StdClass();
        $object->type = $type;
        $object->nullable = $nullable;

        return $object;
    }
}
