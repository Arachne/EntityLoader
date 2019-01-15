<?php

declare(strict_types=1);

namespace Tests\Functional;

use Codeception\Test\Unit;
use Contributte\Codeception\Module\NetteApplicationModule;
use Contributte\Codeception\Module\NetteDIModule;
use Nette\Application\Application;
use Tests\Functional\Fixtures\Article;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityUnloaderTest extends Unit
{
    /**
     * @var NetteApplicationModule&NetteDIModule
     */
    protected $tester;

    public function testLink(): void
    {
        $this->tester->amOnPage('/default');
        $presenter = $this->tester->grabService(Application::class)->getPresenter();
        self::assertSame('/entity?parameter=5', $presenter->link('Article:entity', new Article(5)));
    }
}
