<?php

namespace Tests\Functional;

use Codeception\TestCase\Test;
use Nette\Application\Application;
use Tests\Functional\Fixtures\Article;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityLoaderTest extends Test
{
    public function testUntyped()
    {
        $this->tester->amOnPage('/untyped/5');
        $request = $this->tester->grabService(Application::class)->getPresenter()->getRequest();
        $this->assertSame([
            'action' => 'untyped',
            'parameter' => '5',
        ], $request->getParameters());
    }

    public function testInt()
    {
        $this->tester->amOnPage('/int/5');
        $request = $this->tester->grabService(Application::class)->getPresenter()->getRequest();
        $this->assertSame([
            'action' => 'int',
            'parameter' => 5,
        ], $request->getParameters());
    }

    public function testIntWithDefault()
    {
        $this->tester->amOnPage('/int');
        $request = $this->tester->grabService(Application::class)->getPresenter()->getRequest();
        $this->assertSame([
            'action' => 'int',
            'parameter' => null,
        ], $request->getParameters());
    }

    public function testEntity()
    {
        $this->tester->amOnPage('/entity/5');
        $request = $this->tester->grabService(Application::class)->getPresenter()->getRequest();
        $this->assertEquals([
            'action' => 'entity',
            'parameter' => new Article(5),
        ], $request->getParameters());
    }
}
