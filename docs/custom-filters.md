Custom filters
====

To use objects of a certain class as parameters you will need two services. One implementing `Arachne\EntityLoader\FilterInInterface` with `arachne.entityLoader.filterIn` tag and one implementing `Arachne\EntityLoader\FilterOutInterface` with `arachne.entityLoader.filterOut` tag. For both services pass the classes handled by these services as the tag attribute.

```
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
