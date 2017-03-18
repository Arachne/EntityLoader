<?php

namespace Tests\Unit;

use Arachne\EntityLoader\EntityInterface;
use Arachne\EntityLoader\EntityUnloader;
use Arachne\EntityLoader\FilterOutInterface;
use ArrayObject;
use Codeception\Test\Unit;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Phpunit\Phony;
use Eloquent\Phony\Stub\StubVerifier;
use stdClass;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityUnloaderTest extends Unit
{
    /**
     * @var EntityUnloader
     */
    private $entityUnloader;

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
        $this->filterHandle = Phony::mock(FilterOutInterface::class);
        $this->filterIterator = new ArrayObject();
        $this->entityUnloader = new EntityUnloader($this->filterIterator);
    }

    public function testFilterOut()
    {
        $this->filterIterator[] = $this->filterHandle->get();

        $stub = Phony::stub();

        $this->filterHandle
            ->supports
            ->with(StubVerifier::class)
            ->returns(true);

        $this->filterHandle
            ->filterOut
            ->with($stub, StubVerifier::class)
            ->returns('1');

        $this->assertSame('1', $this->entityUnloader->filterOut($stub));
    }

    public function testFilterOutEntityInterface()
    {
        $this->filterIterator[] = $this->filterHandle->get();

        $entityHandle = Phony::mock(EntityInterface::class);
        $entityHandle
            ->getBaseType
            ->returns(EntityInterface::class);

        $this->filterHandle
            ->supports
            ->with(EntityInterface::class)
            ->returns(true);

        $this->filterHandle
            ->filterOut
            ->with($entityHandle->get(), EntityInterface::class)
            ->returns('1');

        $this->assertSame('1', $this->entityUnloader->filterOut($entityHandle->get()));
    }

    /**
     * @expectedException \Arachne\EntityLoader\Exception\UnexpectedValueException
     * @expectedExceptionMessage No filter out found for type
     */
    public function testFilterNotFound()
    {
        $entityHandle = Phony::mock(stdClass::class);

        $this->entityUnloader->filterOut($entityHandle->get());
    }
}
