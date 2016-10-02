<?php

namespace Tests\Functional\Fixtures;

use Arachne\EntityLoader\Application\EntityLoaderPresenterTrait;
use Arachne\EntityLoader\Application\SecuredLinksPresenterTrait;
use Nette\Application\UI\Presenter;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArticlePresenter extends Presenter
{
    use EntityLoaderPresenterTrait;
    use SecuredLinksPresenterTrait;

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

    /**
     * @secured
     */
    public function handleSignal(Article $parameter)
    {
    }
}
