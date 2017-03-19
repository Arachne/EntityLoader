<?php

declare(strict_types=1);

namespace Tests\Functional;

use Arachne\Codeception\Module\NetteApplicationModule;
use Arachne\Codeception\Module\NetteDIModule;
use Codeception\Test\Unit;
use Nette\Application\Application;
use Tests\Functional\Fixtures\Article;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityUnloaderTest extends Unit
{
    /**
     * @var NetteApplicationModule|NetteDIModule
     */
    protected $tester;

    public function testLink()
    {
        $this->tester->amOnPage('/default');
        $presenter = $this->tester->grabService(Application::class)->getPresenter();
        $this->assertSame('/entity?parameter=5', $presenter->link('Article:entity', new Article(5)));
    }
}
