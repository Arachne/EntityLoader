<?php

namespace Tests\Integration\Classes;

use Exception;
use Nette\Application\UI\Presenter;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArticlePresenter extends Presenter
{

	final public function __construct()
	{
		throw new Exception('This class is there for annotations only.');
	}

	public function actionDetail(Article $entity)
	{
	}

	/**
	 * @param int $parameter
	 */
	public function actionInt($parameter)
	{
	}

	/**
	 * @param string $parameter
	 */
	public function actionString($parameter)
	{
	}

	/**
	 * @param float $parameter
	 */
	public function actionFloat($parameter)
	{
	}

	/**
	 * @param bool $parameter
	 */
	public function actionBool($parameter)
	{
	}

	/**
	 * @param mixed $parameter
	 */
	public function actionMixed($parameter)
	{
	}

	public function actionArray(array $parameter)
	{
	}

	public function actionNullable(Article $entity = NULL)
	{
	}

	public function actionNotNullable(Article $entity)
	{
	}

}
