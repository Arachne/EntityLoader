<?php

declare(strict_types=1);

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

    public function startup(): void
    {
        $this->terminate();
    }

    public function actionUntyped($parameter): void
    {
    }

    public function actionEntity(Article $parameter): void
    {
    }

    public function actionInt(int $parameter = 1): void
    {
    }

    public function actionBool(bool $parameter): void
    {
    }

    public function actionFloat(float $parameter): void
    {
    }

    public function actionString(string $parameter): void
    {
    }

    /**
     * @secured
     */
    public function handleSignal(Article $parameter): void
    {
    }
}
