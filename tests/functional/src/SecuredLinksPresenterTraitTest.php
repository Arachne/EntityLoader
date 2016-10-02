<?php

namespace Tests\Functional;

use Codeception\TestCase\Test;
use Nette\Application\Application;
use Tests\Functional\Fixtures\Article;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class SecuredLinksPresenterTraitTest extends Test
{
    public function testLink()
    {
        $this->tester->amOnPage('/default');
        $presenter = $this->tester->grabService(Application::class)->getPresenter();
        self::assertRegExp('~^/default\\?parameter=4&do=signal&_sec=[a-zA-Z0-9-_]++$~', $presenter->link('signal!', new Article(4)));
    }
}
