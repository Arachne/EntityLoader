<?php

namespace Tests\Integration\Classes;

use Arachne\EntityLoader\Application\RequestEntityUnloader;
use Arachne\EntityLoader\Routing\Route;
use Arachne\EntityLoader\Routing\RouteList;
use Nette\Application\IRouter;
use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RouterFactory extends Object
{

	/** @var RequestEntityUnloader */
	protected $unloader;

	public function __construct(RequestEntityUnloader $unloader)
	{
		$this->unloader = $unloader;
	}

	/**
	 * @return IRouter
	 */
	public function create()
	{
		$router = new RouteList($this->unloader);
		$router[] = new Route('/<action>[/<parameter>]', [
			'presenter' => 'Article',
		]);
		return $router;
	}

}
