<?php

namespace Tests\Unit;

use Arachne\EntityLoader\Application\ParameterFinder;
use Arachne\EntityLoader\Application\RequestEntityLoader;
use Arachne\EntityLoader\EntityLoader;
use Codeception\MockeryModule\Test;
use Mockery;
use Mockery\MockInterface;
use Nette\Application\Request;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityLoaderTest extends Test
{
    /** @var RequestEntityLoader */
    private $requestEntityLoader;

    /** @var MockInterface */
    private $entityLoader;

    /** @var MockInterface */
    private $finder;

    protected function _before()
    {
        $this->finder = Mockery::mock(ParameterFinder::class);
        $this->entityLoader = Mockery::mock(EntityLoader::class);
        $this->requestEntityLoader = new RequestEntityLoader($this->entityLoader, $this->finder);
    }

    public function testFilterIn()
    {
        $expected = [
            'entity' => 'value2',
        ];
        $request = new Request('', 'GET', [
            'entity' => 'value1',
        ]);
        $mapping = [
            'entity' => (object) [
                'type' => 'Type1',
            ]
        ];
        $this->finder->shouldReceive('getMapping')
            ->once()
            ->with($request)
            ->andReturn($mapping);
        $this->entityLoader
            ->shouldReceive('filterIn')
            ->once()
            ->with('Type1', 'value1')
            ->andReturn('value2');
        $this->requestEntityLoader->filterIn($request);
        $this->assertEquals($expected, $request->getParameters());
    }

    public function testFilterInEmptyMapping()
    {
        $expected = [
            'entity' => 'value1',
        ];
        $request = new Request('', 'GET', $expected);
        $mapping = [];
        $this->finder->shouldReceive('getMapping')
            ->once()
            ->with($request)
            ->andReturn($mapping);
        $this->requestEntityLoader->filterIn($request);
        $this->assertEquals($expected, $request->getParameters());
    }

    public function testFilterInNullable()
    {
        $expected = [
            'entity' => null,
        ];
        $request = new Request('', 'GET', [
            'entity' => null,
        ]);
        $mapping = [
            'entity' => (object) [
                'type' => 'Type1',
                'nullable' => true,
            ]
        ];
        $this->finder->shouldReceive('getMapping')
            ->once()
            ->with($request)
            ->andReturn($mapping);
        $this->requestEntityLoader->filterIn($request);
        $this->assertEquals($expected, $request->getParameters());
    }

    /**
     * @expectedException \Arachne\EntityLoader\Exception\UnexpectedValueException
     * @expectedExceptionMessage Parameter 'entity' can't be null.
     */
    public function testFilterInNullableException()
    {
        $request = new Request('', 'GET', [
            'entity' => null,
        ]);
        $mapping = [
            'entity' => (object) [
                'type' => 'Type1',
                'nullable' => false,
            ]
        ];
        $this->finder->shouldReceive('getMapping')
            ->once()
            ->with($request)
            ->andReturn($mapping);
        $this->requestEntityLoader->filterIn($request);
    }
}
