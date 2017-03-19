<?php

declare(strict_types=1);

namespace Tests\Unit;

use Arachne\EntityLoader\Application\ParameterFinder;
use Arachne\EntityLoader\Exception\TypeHintException;
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

    protected function _before(): void
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

    public function testAction(): void
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                'persistent' => 0,
            ]
        );

        self::assertEquals(
            [
                'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
                'actionEntity' => $this->createInfoObject('Tests\Unit\Classes\Class2', false),
                'persistent2' => $this->createInfoObject('string', true),
            ],
            $this->finder->getMapping($request)
        );
    }

    public function testNoAction(): void
    {
        $request = $this->createRequest(
            [
                'persistent' => 0,
            ]
        );

        self::assertEquals(
            [
                'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
                'persistent2' => $this->createInfoObject('string', true),
            ],
            $this->finder->getMapping($request)
        );
    }

    public function testRenderAndHandle(): void
    {
        $request = $this->createRequest(
            [
                'action' => 'testRender',
                'do' => 'testHandle',
            ]
        );

        self::assertEquals(
            [
                'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
                'renderEntity' => $this->createInfoObject('Tests\Unit\Classes\Class3', false),
                'handleEntity' => $this->createInfoObject('Tests\Unit\Classes\Class4', false),
                'persistent2' => $this->createInfoObject('string', true),
            ],
            $this->finder->getMapping($request)
        );
    }

    public function testNoTypehintHandle(): void
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                'do' => 'noTypehintHandle',
            ]
        );

        self::assertEquals(
            [
                'actionEntity' => $this->createInfoObject('Tests\Unit\Classes\Class2', false),
                'handleEntity' => $this->createInfoObject('mixed', false),
                'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
                'persistent2' => $this->createInfoObject('string', true),
            ],
            $this->finder->getMapping($request)
        );
    }

    public function testComponent(): void
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                'do' => 'component-testHandle',
                'component-persistent' => 1,
            ]
        );

        self::assertEquals(
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

    public function testSubComponent(): void
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                'do' => 'component-sub-testHandle',
                'component-sub-persistent' => 1,
            ]
        );

        self::assertEquals(
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

    public function testNamelessComponent(): void
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                '-persistent' => 1,
            ]
        );

        self::assertEquals(
            [
                'persistent1' => $this->createInfoObject('Tests\Unit\Classes\Class1', true),
                'actionEntity' => $this->createInfoObject('Tests\Unit\Classes\Class2', false),
                'persistent2' => $this->createInfoObject('string', true),
            ],
            $this->finder->getMapping($request)
        );
    }

    public function testNonexistentComponent(): void
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                'nonexistentComponent-persistent' => 1,
            ]
        );

        try {
            $this->finder->getMapping($request);
            $this->fail();
        } catch (TypeHintException $e) {
            self::assertSame('Class "Tests\Unit\Classes\NonexistentComponent" from Tests\Unit\Classes\TestPresenter::createComponentNonexistentComponent return type not found.', $e->getMessage());
        }
    }

    public function testMissingTypehint(): void
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                'missingTypehint-persistent' => 1,
            ]
        );

        try {
            $this->finder->getMapping($request);
            $this->fail();
        } catch (TypeHintException $e) {
            self::assertSame('Method Tests\Unit\Classes\TestPresenter::createComponentMissingTypehint has no return type.', $e->getMessage());
        }
    }

    public function testBuiltinTypehint(): void
    {
        $request = $this->createRequest(
            [
                'action' => 'testAction',
                'builtinTypehint-persistent' => 1,
            ]
        );

        try {
            $this->finder->getMapping($request);
            $this->fail();
        } catch (TypeHintException $e) {
            self::assertSame('Method Tests\Unit\Classes\TestPresenter::createComponentBuiltinTypehint does not return a class.', $e->getMessage());
        }
    }

    private function createRequest(array $parameters): Request
    {
        return new Request('', 'GET', $parameters);
    }

    private function createInfoObject(string $type, bool $optional): StdClass
    {
        $object = new StdClass();
        $object->type = $type;
        $object->optional = $optional;

        return $object;
    }
}
