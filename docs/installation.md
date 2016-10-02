Installation
====

The best way to install Arachne/EntityLoader is using [Composer](http://getcomposer.org/).

```sh
$ composer require arachne/entity-loader
```

Now you need to register the necessary extensions using your [neon](http://ne-on.org/) config file.

```
extensions:
    - Oops\CacheFactory\DI\CacheFactoryExtension
    - Arachne\ServiceCollections\DI\ServiceCollectionsExtension
    - Arachne\ContainerAdapter\DI\ContainerAdapterExtension
    - Arachne\EventDispatcher\DI\EventDispatcherExtension
    - Arachne\EntityLoader\DI\EntityLoaderExtension
```

Next add the `Arachne\EntityLoader\Application\EntityLoaderPresenterTrait` to your BasePresenter. It overrides the storeRequest & restoreRequest methods to make them work with object parameters.

```php
abstract class BasePresenter extends \Nette\Application\UI\Presenter
{
    use \Arachne\EntityLoader\Application\EntityLoaderPresenterTrait;
}
```

Finally you will need to wrap your router using `Arachne\EntityLoader\Routing\RouterWrapper`. Beware that it has a dependency on `Arachne\EntityLoader\Application\RequestEntityUnloader`. Below is an example what your RouterFactory could look like:

```php
<?php

namespace App\Routing;

use Arachne\EntityLoader\Application\RequestEntityUnloader;
use Arachne\EntityLoader\Routing\RouterWrapper;
use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory
{
    /**
     * @var RequestEntityUnloader
     */
    private $entityUnloader;

    public function __construct(RequestEntityUnloader $entityUnloader)
    {
        $this->entityUnloader = $entityUnloader;
    }

    /**
     * @return IRouter
     */
    public function create()
    {
        $router = new RouteList();

        $router[] = new Route('<presenter>/<action>[/<entity>]');

        return new RouterWrapper($router, $this->entityUnloader);
    }
}
```
