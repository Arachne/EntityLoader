<?php

declare(strict_types=1);

namespace Tests\Functional;

use Arachne\Codeception\Module\NetteApplicationModule;
use Arachne\Codeception\Module\NetteDIModule;
use Arachne\EntityLoader\Application\EntityLoaderPresenterTrait;
use Codeception\Test\Unit;
use Nette\Application\AbortException;
use Nette\Application\Application;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityLoaderPresenterTraitTest extends Unit
{
    /**
     * @var NetteApplicationModule&NetteDIModule
     */
    protected $tester;

    public function testStoreRestoreRequest(): void
    {
        $this->tester->amOnPage('/entity?parameter=5');
        /** @var EntityLoaderPresenterTrait $presenter */
        $presenter = $this->tester->grabService(Application::class)->getPresenter();
        $key = $presenter->storeRequest();
        try {
            $presenter->restoreRequest($key);
            self::fail();
        } catch (AbortException $e) {
        }
    }
}
