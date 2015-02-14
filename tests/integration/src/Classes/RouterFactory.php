<?php

namespace Tests\Integration\Classes;

use Arachne\EntityLoader\Application\Envelope;
use Arachne\EntityLoader\Application\RequestEntityLoader;
use Arachne\EntityLoader\Application\Route;
use Arachne\EntityLoader\Application\RouteList;
use Nette\Application\IRouter;
use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RouterFactory extends Object
{

	/** @var RequestEntityLoader */
	protected $loader;

	public function __construct(RequestEntityLoader $loader)
	{
		$this->loader = $loader;
	}

	/**
	 * @return IRouter
	 */
	public function create()
	{
		$router = new RouteList($this->loader);
		$router[] = new Route('/wrong', [
			'presenter' => 'Wrong',
			'action' => 'detail',
		]);
		$router[] = new Route('/noaction', [
			'presenter' => 'Wrong',
		]);
		$router[] = new Route('/detail/<entity>', [
			'presenter' => 'Article',
			'action' => 'detail',
			'entity' => [
				Route::FILTER_OUT => function (Envelope $envelope) {
					return 'article-' . $envelope->getObject()->getValue();
				},
			],
		]);
		$router[] = new Route('/array', [
			'presenter' => 'Article',
			'action' => 'array',
		]);
		$router[] = new Route('/<action>[/<parameter>]', [
			'presenter' => 'Article',
		]);
		return $router;
	}

}
