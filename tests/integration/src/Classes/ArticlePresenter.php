<?php

namespace Tests\Integration\Classes;

use Nette\Application\UI\Presenter;
use Nette\StaticClassException;

/**
 * @author Jáchym Toušek
 */
class ArticlePresenter extends Presenter
{

	final public function __construct()
	{
		throw new StaticClassException('This class is there for type-hints only.');
	}

	public function actionDetail(Article $entity)
	{
	}

}
