Doctrine integration
====

For usage with Doctrine ORM entities add the [Arachne/Doctrine](https://github.com/Arachne/Doctrine) package to your application.

```
$ composer require arachne/doctrine
```

Note that in your [neon](http://ne-on.org/) config file `Arachne\Doctrine\DI\DoctrineExtension` has to be registered before `Arachne\EntityLoader\DI\EntityLoaderExtension`.

```
extensions:
    # ...
    - Arachne\Doctrine\DI\DoctrineExtension
    - Arachne\EntityLoader\DI\EntityLoaderExtension
```
