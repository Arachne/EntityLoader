<?php

namespace Tests\Functional;

use Arachne\Codeception\Module\NetteApplicationModule;
use Arachne\Codeception\Module\NetteDIModule;
use Codeception\Test\Unit;
use Nette\Application\Application;
use Nette\Application\UI\Presenter;
use Tests\Functional\Fixtures\Article;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityLoaderTest extends Unit
{
    /**
     * @var NetteApplicationModule|NetteDIModule
     */
    protected $tester;

    public function testUntyped()
    {
        $this->tester->amOnPage('/untyped?parameter=5');
        $request = $this->tester->grabService(Application::class)->getPresenter()->getRequest();
        $this->assertSame([
            'action' => 'untyped',
            'parameter' => '5',
        ], $request->getParameters());
    }

    public function testEntity()
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

    public function testInt()
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

    public function testIntWithDefault()
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

    /**
     * @expectedException \Nette\Application\BadRequestException
     */
    public function testIntError()
    {
        $this->tester->amOnPage('/int?parameter[]=0');
    }

    public function testBool()
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

    /**
     * @expectedException \Nette\Application\BadRequestException
     */
    public function testBoolError()
    {
        $this->tester->amOnPage('/bool?parameter[]=0');
    }

    public function testFloat()
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

    /**
     * @expectedException \Nette\Application\BadRequestException
     */
    public function testFloatError()
    {
        $this->tester->amOnPage('/float?parameter[]=0');
    }

    public function testString()
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

    /**
     * @expectedException \Nette\Application\BadRequestException
     */
    public function testStringError()
    {
        $this->tester->amOnPage('/string?parameter[]=0');
    }
}
