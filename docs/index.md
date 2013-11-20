# Documentation

This extension is here to provide an easy way to have objects in application request and consequently in parameters of presenters and components.


## Installation

The best way to install Arachne/EntityLoader is using [Composer](http://getcomposer.org/):

```sh
$ composer require arachne/entity-loader
```

Now you need to register Arachne/EntityLoader extension using your [neon](http://ne-on.org/) config file.

```
extensions:
	arachne.entityLoader: Arachne\EntityLoader\DI\EntityLoaderExtension
```

### PHP 5.4

Finally add the Arachne\EntityLoader\Application\TEntityLoaderPresenter trait to your BasePresenter.

```php
abstract class BasePresenter extends \Nette\Application\UI\Presenter
{

	use \Arachne\Verifier\Application\TEntityLoaderPresenter;

}
```

### PHP 5.3

If you don't use PHP 5.4, just copy the trait's methods to your BasePresenter.


## Usage

You will need a service implementing Arachne\EntityLoader\IConverter with arachne.entityLoader.converter tag and specified conversion types in the tag attributes. This will be often provided by some other extension but let's show it on an easy example.

### Implementation

```php
class DateTimeConverter extends \Nette\Object implements \Arachne\EntityLoader\IConverter
{

	public function canConvert($type)
	{
		return is_subclass_of($type, 'DateTimeInterface');
	}

	public function parameterToEntity($type, $value)
	{
		// $type is DateTime or DateTimeImmutable
		if (is_numeric($value)) { // timestamp
			return new $type(date('Y-m-d H:i:s', $value));
		} else { // textual
			return new $type($value);
		}
	}

	public function entityToParameter($type, $entity)
	{
		// $entity instanceof $type
		return $entity->getTimestamp();
	}

}
```

You could argue that parameterToEntity method should only handle timestamps and not other formats. This implementation is intentional and the usage will be addressed later.


### Configuration

Now we can register this converter in config.neon:

```
services:
	dateTimeCoverter:
		class: Arachne\EntityLoader\IConverter
		factory: DateTimeConverter
		tags:
			arachne.entityLoader.converter:
```

### Presenter

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

### Routers

Now what if we want to use different format than timestamp in URL? This is supported and actually it can even be specific to the route in question. The solution uses filter out option and envelope object:

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
