<?php

declare(strict_types=1);

namespace Tests\Functional;

use Codeception\Test\Unit;
use Contributte\Codeception\Module\NetteApplicationModule;
use Contributte\Codeception\Module\NetteDIModule;
use Nette\Application\Application;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Tests\Functional\Fixtures\Article;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityLoaderTest extends Unit
{
    /**
     * @var NetteApplicationModule&NetteDIModule
     */
    protected $tester;

    public function testUntyped(): void
    {
        $this->tester->amOnPage('/untyped?parameter=5');
        $request = $this->tester->grabService(Application::class)->getPresenter()->getRequest();
        self::assertSame(
            [
                'action' => 'untyped',
                'parameter' => '5',
            ],
            $request->getParameters()
        );
    }

    public function testEntity(): void
    {
        $this->tester->amOnPage('/entity?parameter=5');
        $presenter = $this->tester->grabService(Application::class)->getPresenter();
        self::assertInstanceOf(Presenter::class, $presenter);
        $request = $presenter->getRequest();
        self::assertEquals(
            [
                'action' => 'entity',
                'parameter' => new Article(5),
            ],
            $request->getParameters()
        );
    }

    public function testInt(): void
    {
        $this->tester->amOnPage('/int?parameter=5');
        $presenter = $this->tester->grabService(Application::class)->getPresenter();
        self::assertInstanceOf(Presenter::class, $presenter);
        $request = $presenter->getRequest();
        self::assertSame(
            [
                'action' => 'int',
                'parameter' => 5,
            ],
            $request->getParameters()
        );
    }

    public function testIntWithDefault(): void
    {
        $this->tester->amOnPage('/int');
        $presenter = $this->tester->grabService(Application::class)->getPresenter();
        self::assertInstanceOf(Presenter::class, $presenter);
        $request = $presenter->getRequest();
        self::assertSame(
            [
                'action' => 'int',
            ],
            $request->getParameters()
        );
    }

    public function testIntError(): void
    {
        try {
            $this->tester->amOnPage('/int?parameter[]=0');
            self::fail();
        } catch (BadRequestException $e) {
        }
    }

    public function testBool(): void
    {
        $this->tester->amOnPage('/bool?parameter=1');
        $presenter = $this->tester->grabService(Application::class)->getPresenter();
        self::assertInstanceOf(Presenter::class, $presenter);
        $request = $presenter->getRequest();
        self::assertSame(
            [
                'action' => 'bool',
                'parameter' => true,
            ],
            $request->getParameters()
        );
    }

    public function testBoolError(): void
    {
        try {
            $this->tester->amOnPage('/bool?parameter[]=0');
            self::fail();
        } catch (BadRequestException $e) {
        }
    }

    public function testFloat(): void
    {
        $this->tester->amOnPage('/float?parameter=1');
        $presenter = $this->tester->grabService(Application::class)->getPresenter();
        self::assertInstanceOf(Presenter::class, $presenter);
        $request = $presenter->getRequest();
        self::assertSame(
            [
                'action' => 'float',
                'parameter' => 1.0,
            ],
            $request->getParameters()
        );
    }

    public function testFloatError(): void
    {
        try {
            $this->tester->amOnPage('/float?parameter[]=0');
            self::fail();
        } catch (BadRequestException $e) {
        }
    }

    public function testString(): void
    {
        $this->tester->amOnPage('/string?parameter=1');
        $presenter = $this->tester->grabService(Application::class)->getPresenter();
        self::assertInstanceOf(Presenter::class, $presenter);
        $request = $presenter->getRequest();
        self::assertSame(
            [
                'action' => 'string',
                'parameter' => '1',
            ],
            $request->getParameters()
        );
    }

    public function testStringError(): void
    {
        try {
            $this->tester->amOnPage('/string?parameter[]=0');
            self::fail();
        } catch (BadRequestException $e) {
        }
    }
}
