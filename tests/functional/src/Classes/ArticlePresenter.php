<?php

namespace Tests\Functional\Classes;

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

    /**
     * @param int $parameter
     */
    public function actionInt($parameter = 1)
    {
    }

    public function actionEntity(Article $parameter)
    {
    }
}
