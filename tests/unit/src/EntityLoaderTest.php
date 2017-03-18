<?php

namespace Tests\Unit;

use Arachne\EntityLoader\EntityLoader;
use Arachne\EntityLoader\FilterInInterface;
use ArrayObject;
use Codeception\Test\Unit;
use DateTime;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Phpunit\Phony;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityLoaderTest extends Unit
{
    /**
     * @var EntityLoader
     */
    private $entityLoader;

    /**
     * @var InstanceHandle
     */
    private $filterHandle;

    /**
     * @var ArrayObject
     */
    private $filterIterator;

    protected function _before()
    {
        $this->filterHandle = Phony::mock(FilterInInterface::class);
        $this->filterIterator = new ArrayObject();
        $this->entityLoader = new EntityLoader($this->filterIterator);
    }

    public function testFilterIn()
    {
        $this->filterIterator[] = $this->filterHandle->get();

        $mock1 = Phony::mock(DateTime::class)->get();

        $this->filterHandle
            ->supports
            ->with(DateTime::class)
            ->returns(true);

        $this->filterHandle
            ->filterIn
            ->returns($mock1);

        $this->assertSame($mock1, $this->entityLoader->filterIn(DateTime::class, 1));

        $this->filterHandle
            ->filterIn
            ->calledWith(1, DateTime::class);
    }

    /**
     * @expectedException \Arachne\EntityLoader\Exception\UnexpectedValueException
     * @expectedExceptionMessage FilterIn did not return an instance of "DateTime".
     */
    public function testFilterInFail()
    {
        $this->filterIterator[] = $this->filterHandle->get();

        $this->filterHandle
            ->supports
            ->with(DateTime::class)
            ->returns(true);

        $this->filterHandle
            ->filterIn
            ->returns(null);

        $this->entityLoader->filterIn(DateTime::class, 1);
    }

    public function testFilterInIgnore()
    {
        // Make sure that the converter is not called at all if the parameter already has the desired type.
        $mock1 = Phony::mock(DateTime::class)->get();
        $this->assertSame($mock1, $this->entityLoader->filterIn(DateTime::class, $mock1));
    }

    /**
     * @expectedException \Arachne\EntityLoader\Exception\UnexpectedValueException
     * @expectedExceptionMessage No filter in found for type "DateTime".
     */
    public function testFilterNotFound()
    {
        $parameters = [
            'entity' => 'value1',
        ];

        $this->entityLoader->filterIn(DateTime::class, $parameters);
    }
}
