<?php

namespace Tests\Functional\Fixtures;

use Arachne\EntityLoader\Routing\Route;
use Nette\Application\IRouter;
use Nette\Application\Routers\RouteList;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RouterFactory
{
    public function create() : IRouter
    {
        $router = new RouteList();
        $router[] = new Route(
            '/<action>',
            [
                'presenter' => 'Article',
            ]
        );

        return $router;
    }
}
