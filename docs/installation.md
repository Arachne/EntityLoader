Installation
====

The best way to install Arachne/EntityLoader is using [Composer](http://getcomposer.org/).

```sh
composer require arachne/entity-loader
```

Now you need to register the necessary extensions using your [neon](http://ne-on.org/) config file.

```
extensions:
    oops.cachefactory: Oops\CacheFactory\DI\CacheFactoryExtension
    symplify.eventdispatcher: Symplify\SymfonyEventDispatcher\Adapter\Nette\DI\SymfonyEventDispatcherExtension
    arachne.servicecollections: Arachne\ServiceCollections\DI\ServiceCollectionsExtension
    arachne.entityLoader: Arachne\EntityLoader\DI\EntityLoaderExtension
```


Presenters and components
----

Next add the `Arachne\EntityLoader\Application\EntityLoaderPresenterTrait` to your BasePresenter. It overrides the storeRequest & restoreRequest methods to make them work with object parameters.

```php
use Arachne\EntityLoader\Application\EntityLoaderPresenterTrait;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
    use EntityLoaderPresenterTrait;
}
```

Finally you will need to specify the types of parameters in your application.

- Add types to all parameters of your action, render and handle methods.
- Add `@var` annotations to all your persistent parameters.
- Add return types to all your createComponent methods.

These types are handled out of the box: `int`, `bool`, `float`, `string`, `array` and `mixed`.

EntityLoader is very strict about this and it will throw an exception if some type or annotation is missing.


Nextras/SecuredLinks
----

If you're using [Nextras/SecuredLinks](https://github.com/nextras/secured-links) replace the standard `Nextras\Application\UI\SecuredLinksPresenterTrait` with `Arachne\EntityLoader\Application\SecuredLinksPresenterTrait`.
