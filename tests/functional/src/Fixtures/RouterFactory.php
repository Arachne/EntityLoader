<?php

namespace Tests\Functional\Fixtures;

use Arachne\EntityLoader\Application\RequestEntityUnloader;
use Arachne\EntityLoader\Routing\Route;
use Arachne\EntityLoader\Routing\RouterWrapper;
use Nette\Application\IRouter;
use Nette\Application\Routers\RouteList;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RouterFactory
{
    /**
     * @var RequestEntityUnloader
     */
    protected $unloader;

    public function __construct(RequestEntityUnloader $unloader)
    {
        $this->unloader = $unloader;
    }

    public function create() : IRouter
    {
        $router = new RouteList();
        $router[] = new Route(
            '/<action>',
            [
                'presenter' => 'Article',
            ]
        );

        return new RouterWrapper($router, $this->unloader);
    }
}
