<?php

namespace Tests\Functional;

use Codeception\TestCase\Test;
use Nette\Application\Application;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityLoaderPresenterTraitTest extends Test
{
    /**
     * @expectedException \Nette\Application\AbortException
     */
    public function testStoreRestoreRequest()
    {
        $this->tester->amOnPage('/entity?parameter=5');
        $presenter = $this->tester->grabService(Application::class)->getPresenter();
        $key = $presenter->storeRequest();
        $presenter->restoreRequest($key);
    }
}
