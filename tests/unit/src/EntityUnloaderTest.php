<?php

namespace Tests\Unit;

use Arachne\EntityLoader\EntityInterface;
use Arachne\EntityLoader\EntityUnloader;
use Arachne\EntityLoader\FilterOutInterface;
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
     * @var StubVerifier
     */
    private $filterResolver;

    protected function _before()
    {
        $this->filterHandle = Phony::mock(FilterOutInterface::class);
        $this->filterResolver = Phony::stub();
        $this->entityUnloader = new EntityUnloader($this->filterResolver);
    }

    public function testFilterOut()
    {
        $this->filterResolver
            ->returns($this->filterHandle->get());

        $stub = Phony::stub();

        $this->filterHandle
            ->filterOut
            ->with($stub)
            ->returns('1');

        $this->assertSame('1', $this->entityUnloader->filterOut($stub));
    }

    public function testFilterOutEntityInterface()
    {
        $this->filterResolver
            ->returns($this->filterHandle->get());

        $entityHandle = Phony::mock(EntityInterface::class);
        $entityHandle
            ->getBaseType
            ->returns(EntityInterface::class);

        $this->filterHandle
            ->filterOut
            ->with($entityHandle->get())
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

        $this->filterResolver
            ->returns();

        $this->entityUnloader->filterOut($entityHandle->get());
    }
}
