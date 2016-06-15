<?php

namespace Tests\Unit;

use Arachne\DIHelpers\ResolverInterface;
use Arachne\EntityLoader\EntityInterface;
use Arachne\EntityLoader\EntityUnloader;
use Arachne\EntityLoader\FilterOutInterface;
use Codeception\MockeryModule\Test;
use Mockery;
use Mockery\MockInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityUnloaderTest extends Test
{
    /**
     * @var EntityUnloader
     */
    private $entityUnloader;

    /**
     * @var MockInterface
     */
    private $filter;

    /**
     * @var MockInterface
     */
    private $filterResolver;

    protected function _before()
    {
        $this->filter = Mockery::mock(FilterOutInterface::class);
        $this->filterResolver = Mockery::mock(ResolverInterface::class);
        $this->entityUnloader = new EntityUnloader($this->filterResolver);
    }

    public function testFilterOut()
    {
        $this->filterResolver
            ->shouldReceive('resolve')
            ->once()
            ->andReturn($this->filter);

        $mock1 = Mockery::mock();

        $this->filter
            ->shouldReceive('filterOut')
            ->once()
            ->with($mock1)
            ->andReturn('1');

        $this->assertSame('1', $this->entityUnloader->filterOut($mock1));
    }

    public function testFilterOutEntityInterface()
    {
        $this->filterResolver
            ->shouldReceive('resolve')
            ->with(EntityInterface::class)
            ->once()
            ->andReturn($this->filter);

        $mock1 = Mockery::mock(EntityInterface::class);
        $mock1
            ->shouldReceive('getBaseType')
            ->once()
            ->andReturn(EntityInterface::class);

        $this->filter
            ->shouldReceive('filterOut')
            ->once()
            ->with($mock1)
            ->andReturn('1');

        $this->assertSame('1', $this->entityUnloader->filterOut($mock1));
    }

    /**
     * @expectedException Arachne\EntityLoader\Exception\UnexpectedValueException
     * @expectedExceptionMessage No filter out found for type
     */
    public function testFilterNotFound()
    {
        $mock1 = Mockery::mock('Type1');

        $this->filterResolver
            ->shouldReceive('resolve')
            ->once()
            ->andReturn();

        $this->entityUnloader->filterOut($mock1);
    }
}
