<?php

namespace Tests\Unit;

use Arachne\EntityLoader\EntityLoader;
use Arachne\EntityLoader\FilterInInterface;
use Codeception\Test\Unit;
use DateTime;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Phpunit\Phony;
use Eloquent\Phony\Stub\StubVerifier;

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
     * @var StubVerifier
     */
    private $filterResolver;

    protected function _before()
    {
        $this->filterHandle = Phony::mock(FilterInInterface::class);
        $this->filterResolver = Phony::stub();
        $this->entityLoader = new EntityLoader($this->filterResolver);
    }

    public function testFilterIn()
    {
        $this->filterResolver
            ->returns($this->filterHandle->get());

        $mock1 = Phony::mock(DateTime::class)->get();

        $this->filterHandle
            ->filterIn
            ->returns($mock1);

        $this->assertSame($mock1, $this->entityLoader->filterIn(DateTime::class, 1));

        $this->filterHandle
            ->filterIn
            ->calledWith(1);
    }

    /**
     * @expectedException \Arachne\EntityLoader\Exception\UnexpectedValueException
     * @expectedExceptionMessage FilterIn did not return an instance of 'DateTime'.
     */
    public function testFilterInFail()
    {
        $this->filterResolver
            ->returns($this->filterHandle->get());

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
     * @expectedExceptionMessage No filter in found for type 'DateTime'.
     */
    public function testFilterNotFound()
    {
        $parameters = [
            'entity' => 'value1',
        ];

        $this->entityLoader->filterIn(DateTime::class, $parameters);
    }
}
