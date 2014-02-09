<?php

namespace Tests\Integration;

use Arachne\EntityLoader\Application\RouteList;
use Arachne\EntityLoader\EntityEnvelope;
use Arachne\EntityLoader\EntityLoader;
use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class RouterFactory extends Object
{

	/** @var EntityLoader */
	protected $loader;

	public function __construct(EntityLoader $loader)
	{
		$this->loader = $loader;
	}

	/**
	 * @return IRouter
	 */
	public function create()
	{
		$router = new RouteList($this->loader);
		$router[] = new Route('/<entity>', [
			'presenter' => 'Article',
			'action' => 'detail',
			'entity' => [
				Route::FILTER_OUT => function (EntityEnvelope $value) {
					return 'article-' . $value->getEntity()->getValue();
				},
			],
		]);
		return $router;
	}

}
