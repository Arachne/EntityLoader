<?php

namespace Tests\Functional\Fixtures;

use Nette\Application\UI\Presenter;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArticlePresenter extends Presenter
{
    public function startup()
    {
        $this->terminate();
    }

    public function actionUntyped($parameter)
    {
    }

    public function actionEntity(Article $parameter)
    {
    }

    public function actionInt(int $parameter = 1)
    {
    }

    public function actionBool(bool $parameter)
    {
    }

    public function actionFloat(float $parameter)
    {
    }

    public function actionString(string $parameter)
    {
    }
}
