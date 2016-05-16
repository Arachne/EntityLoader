Documentation
====

This extension is here to provide an easy way to have objects in application request and consequently in parameters of presenters and components.


Installation
----

The best way to install Arachne/EntityLoader is using [Composer](http://getcomposer.org/).

```sh
$ composer require arachne/entity-loader
```

Now you need to register Arachne/EntityLoader extension using your [neon](http://ne-on.org/) config file.

```
extensions:
	arachne.entityLoader: Arachne\EntityLoader\DI\EntityLoaderExtension
```

Add the `Arachne\EntityLoader\Application\EntityLoaderPresenterTrait` to your BasePresenter. It overrides the storeRequest & restoreRequest methods to make them work with object parameters. 

```php
abstract class BasePresenter extends \Nette\Application\UI\Presenter
{
	use \Arachne\EntityLoader\Application\EntityLoaderPresenterTrait;
}
```

Finally you will need to change your routing a little. Instead of `Nette\Application\Routers\Route` use `Arachne\EntityLoader\Routing\Route`. Also by default in Nette the root router is an instance of `Nette\Application\Routers\RouteList`. Change it to `Arachne\EntityLoader\Routing\RouteList` (beware that it has a dependency on `Arachne\EntityLoader\Application\RequestEntityUnloader`). For nested route lists stick to the normal `Nette\Application\Routers\RouteList`. The replacement should only be used for 


Doctrine
----

For usage with Doctrine entities just skip this part and add the Arachne/Doctrine package to your application.


Custom filters
----

To use objects of a certain class as parameters you will need a two services. One implementing `Arachne\EntityLoader\FilterInInterface` with `arachne.entityLoader.filterIn` tag and one implementing `Arachne\EntityLoader\FilterOutInterface` with `arachne.entityLoader.filterOut` tag. For both services pass the classes handled by these services as the tag attribute.

```yml
services:
	dateTimeFilterIn:
		class: DateTimeFilterIn
		tags:
			arachne.entityLoader.filterIn: DateTime
	dateTimeFilterOut:
		class: DateTimeFilterOut
		tags:
			arachne.entityLoader.filterOut: DateTime
```

```php
class DateTimeFilterIn implements \Arachne\EntityLoader\FilterInInterface
{
	public function filterIn($value)
	{
		if (is_numeric($value)) { // timestamp
			return new \DateTime(date('Y-m-d H:i:s', $value));
		} else { // textual
			return new \DateTime($value);
		}
	}
}

class DateTimeFilterOut implements \Arachne\EntityLoader\FilterInInterface
{
	public function filterOut($entity)
	{
		// $entity instanceof DateTime
		return $entity->getTimestamp();
	}
}
```

You could argue that filterIn method should only handle timestamps and not other formats because filterOut always returns a timestamp. The point is that filterOut should always return an identifier from which the object can be recreated. Regardless how you wish to represent the parameter in the URL. You can change the URL representation in router (see the example below).


Presenter
----

Finally we can use DateTime parameters in presenter:

```php
class FooPresenter extends \Nette\Application\UI\Presenter
{
	public function renderDefault(DateTime $date)
	{
		$this->template->date = $date;
	}
}
```

Persistent parameters are also supported. Use the @var annotation to specify the class.


Routers
----

Now what if we want to use different format than timestamp in URL? For example imagine that you don't need the time part in one case when you use a DateTime parameter and want to reptesent the date simply as 'Y-m-d' in the URL. This is supported and it can even be specific to the route in question. The solution uses filter out option and envelope object (EntityLoader creates them by default):

```php
$router[] = new Route('/<date>', [
	'presenter' => 'Foo',
	'date' => [
		Route::FILTER_OUT => function ($value) {
			// $value instanceof Arachne\EntityLoader\EntityEnvelope
			return $value->getEntity()->format('Y-m-d');
		},
	],
]);
```
