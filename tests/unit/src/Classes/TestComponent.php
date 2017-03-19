<?php

declare(strict_types=1);

namespace Tests\Unit\Classes;

use Nette\Application\UI\PresenterComponent;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class TestComponent extends PresenterComponent
{
    /**
     * @persistent
     *
     * @var Class5
     */
    public $persistent;

    public function handleTestHandle(Class6 $handleEntity): void
    {
    }

    public function createComponentSub(): TestComponent
    {
    }
}
