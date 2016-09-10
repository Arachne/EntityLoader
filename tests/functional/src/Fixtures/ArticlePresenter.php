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

    /**
     * @param int $parameter
     */
    public function actionInt($parameter = 1)
    {
    }

    /**
     * @param bool $parameter
     */
    public function actionBool($parameter)
    {
    }

    /**
     * @param float $parameter
     */
    public function actionFloat($parameter)
    {
    }

    /**
     * @param string $parameter
     */
    public function actionString($parameter)
    {
    }
}
