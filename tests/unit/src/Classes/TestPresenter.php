<?php

declare(strict_types=1);

namespace Tests\Unit\Classes;

use Nette\Application\UI\Presenter;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class TestPresenter extends Presenter
{
    /**
     * @persistent
     *
     * @var Class1
     */
    public $persistent1;

    /**
     * @persistent
     *
     * @var string
     */
    public $persistent2;

    public function actionTestAction(Class2 $actionEntity): void
    {
    }

    public function renderTestRender(Class3 $renderEntity): void
    {
    }

    public function handleTestHandle(Class4 $handleEntity): void
    {
    }

    public function handleNoTypehintHandle($handleEntity): void
    {
    }

    public function actionNonexistentParameter($entity): void
    {
    }

    protected function createComponentComponent(): TestComponent
    {
    }

    protected function createComponentNonexistentComponent(): NonexistentComponent
    {
    }

    protected function createComponentMissingTypehint()
    {
    }

    protected function createComponentBuiltinTypehint(): int
    {
    }
}
