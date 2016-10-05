Installation
====

The best way to install Arachne/EntityLoader is using [Composer](http://getcomposer.org/).

```sh
$ composer require arachne/entity-loader
```

Now you need to register the necessary extensions using your [neon](http://ne-on.org/) config file.

```
extensions:
    oops.cache_factory: Oops\CacheFactory\DI\CacheFactoryExtension
    arachne.service_collections: Arachne\ServiceCollections\DI\ServiceCollectionsExtension
    arachne.container_adapter: Arachne\ContainerAdapter\DI\ContainerAdapterExtension
    arachne.event_dispatcher: Arachne\EventDispatcher\DI\EventDispatcherExtension
    arachne.entity_loader: Arachne\EntityLoader\DI\EntityLoaderExtension
```

Routing
----

Then you will need to wrap your router using `Arachne\EntityLoader\Routing\RouterWrapper`. Beware that it has a dependency on `Arachne\EntityLoader\Application\RequestEntityUnloader`. Below is an example what your RouterFactory could look like:

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

Presenters and components
----

Next add the `Arachne\EntityLoader\Application\EntityLoaderPresenterTrait` to your BasePresenter. It overrides the storeRequest & restoreRequest methods to make them work with object parameters.

```php
abstract class BasePresenter extends \Nette\Application\UI\Presenter
{
    use \Arachne\EntityLoader\Application\EntityLoaderPresenterTrait;
}
```

Finally you will need to specify the types of parameters in your application.

- Add types or `@param` annotations to all your action, render and handle methods.
- Add `@var` annotations to all your persistent parameters.
- Add `@return` annotations to all your createComponent methods.

These types are handled out of the box: `int`, `bool`, `float`, `string`, `array` and `mixed`.

EntityLoader is very strict about this and it will throw an exception if some annotation is missing.

Nextras/SecuredLinks
----

If you're using [Nextras/SecuredLinks](https://github.com/nextras/secured-links) replace the standard `Nextras\Application\UI\SecuredLinksPresenterTrait` with `Arachne\EntityLoader\Application\SecuredLinksPresenterTrait`.
