<?php

namespace Tests\Functional;

use Codeception\TestCase\Test;
use Nette\Application\Application;
use Tests\Functional\Fixtures\Article;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityUnloaderTest extends Test
{
    public function testLink()
    {
        $this->tester->amOnPage('/default');
        $presenter = $this->tester->grabService(Application::class)->getPresenter();
        $this->assertSame('/entity?parameter=5', $presenter->link('Article:entity', new Article(5)));
    }
}
