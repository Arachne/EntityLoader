<?php

namespace Tests\Unit;

use Arachne\EntityLoader\Application\Envelope;
use Arachne\EntityLoader\Application\RequestEntityUnloader;
use Arachne\EntityLoader\EntityUnloader;
use Codeception\Test\Unit;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Phpunit\Phony;
use Nette\Application\Request;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityUnloaderTest extends Unit
{
    /**
     * @var RequestEntityUnloader
     */
    private $requestEntityUnloader;

    /**
     * @var InstanceHandle
     */
    private $entityUnloaderHandle;

    protected function _before()
    {
        $this->entityUnloaderHandle = Phony::mock(EntityUnloader::class);
        $this->requestEntityUnloader = new RequestEntityUnloader($this->entityUnloaderHandle->get());
    }

    public function testFilterOut()
    {
        $stub = Phony::stub();
        $request = $this->createRequest($stub);

        $this->entityUnloaderHandle
            ->filterOut
            ->with($stub)
            ->returns('value');

        $this->requestEntityUnloader->filterOut($request);

        self::assertSame(
            [
               'entity' => 'value',
            ],
            $request->getParameters()
        );
    }

    public function testFilterOutEmptyMapping()
    {
        $request = $this->createRequest('value');

        $this->requestEntityUnloader->filterOut($request);

        self::assertSame(
            [
               'entity' => 'value',
            ],
            $request->getParameters()
        );
    }

    public function testFilterOutEnvelopes()
    {
        $stub = Phony::stub();
        $request = $this->createRequest($stub);

        $this->entityUnloaderHandle
            ->filterOut
            ->with($stub)
            ->returns('value');

        $this->requestEntityUnloader->filterOut($request, true);

        self::assertEquals(
            [
               'entity' => new Envelope($stub, 'value'),
            ],
            $request->getParameters()
        );
    }

    public function testFilterOutNullable()
    {
        $request = $this->createRequest();

        $this->requestEntityUnloader->filterOut($request);

        self::assertSame(
            [
               'entity' => null,
            ],
            $request->getParameters()
        );
    }

    /**
     * @param mixed $value
     *
     * @return Request
     */
    private function createRequest($value = null)
    {
        return new Request(
            '',
            'GET',
            [
                'entity' => $value,
            ]
        );
    }
}
