<?php

namespace Tests\Functional;

use Codeception\TestCase\Test;
use Nette\Application\Application;
use Tests\Functional\Classes\Article;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityLoaderTest extends Test
{
    /**
     * @expectedException Arachne\EntityLoader\Exception\TypeHintException
     * @expectedExceptionMessage No type hint found for $parameter in Tests\Functional\Classes\ArticlePresenter::actionUntyped(). Specify it or use '@param mixed $parameter' to allow any type.
     */
    public function testUntyped()
    {
        $this->guy->amOnPage('/untyped/5');
    }

    public function testInt()
    {
        $this->guy->amOnPage('/int/5');
        $request = $this->guy->grabService(Application::class)->getPresenter()->getRequest();
        $this->assertSame([
            'action' => 'int',
            'parameter' => 5,
        ], $request->getParameters());
    }

    public function testIntWithDefault()
    {
        $this->guy->amOnPage('/int');
        $request = $this->guy->grabService(Application::class)->getPresenter()->getRequest();
        $this->assertSame([
            'action' => 'int',
            'parameter' => null,
        ], $request->getParameters());
    }

    public function testEntity()
    {
        $this->guy->amOnPage('/entity/5');
        $request = $this->guy->grabService(Application::class)->getPresenter()->getRequest();
        $this->assertEquals([
            'action' => 'entity',
            'parameter' => new Article(5),
        ], $request->getParameters());
    }
}
