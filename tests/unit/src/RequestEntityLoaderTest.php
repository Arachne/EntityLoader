<?php

declare(strict_types=1);

namespace Tests\Unit;

use Arachne\EntityLoader\Application\ParameterFinder;
use Arachne\EntityLoader\Application\RequestEntityLoader;
use Arachne\EntityLoader\EntityLoader;
use Arachne\EntityLoader\Exception\UnexpectedValueException;
use Codeception\Test\Unit;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Phpunit\Phony;
use Nette\Application\Request;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityLoaderTest extends Unit
{
    /**
     * @var RequestEntityLoader
     */
    private $requestEntityLoader;

    /**
     * @var InstanceHandle
     */
    private $entityLoaderHandle;

    /**
     * @var InstanceHandle
     */
    private $finderHandle;

    protected function _before(): void
    {
        $this->finderHandle = Phony::mock(ParameterFinder::class);
        $this->entityLoaderHandle = Phony::mock(EntityLoader::class);
        $this->requestEntityLoader = new RequestEntityLoader($this->entityLoaderHandle->get(), $this->finderHandle->get());
    }

    public function testFilterIn(): void
    {
        $request = $this->createRequest('value1');

        $this->finderHandle
            ->getMapping
            ->with($request)
            ->returns(
                [
                    'entity' => (object) [
                        'type' => 'Type1',
                    ],
                ]
            );

        $this->entityLoaderHandle
            ->filterIn
            ->with('Type1', 'value1')
            ->returns('value2');

        $this->requestEntityLoader->filterIn($request);

        self::assertSame(
            [
                'entity' => 'value2',
            ],
            $request->getParameters()
        );
    }

    public function testFilterInEmptyMapping(): void
    {
        $request = $this->createRequest('value1');

        $this->finderHandle
            ->getMapping
            ->with($request)
            ->returns([]);

        $this->requestEntityLoader->filterIn($request);

        self::assertSame(
            [
                'entity' => 'value1',
            ],
            $request->getParameters()
        );
    }

    public function testFilterInNullable(): void
    {
        $request = $this->createRequest(null);

        $this->finderHandle
            ->getMapping
            ->with($request)
            ->returns(
                [
                    'entity' => (object) [
                        'type' => 'Type1',
                        'optional' => true,
                    ],
                ]
            );

        $this->requestEntityLoader->filterIn($request);

        self::assertSame(
            [
               'entity' => null,
            ],
            $request->getParameters()
        );
    }

    public function testFilterInNullableException(): void
    {
        $request = $this->createRequest(null);

        $this->finderHandle
            ->getMapping
            ->with($request)
            ->returns(
                [
                    'entity' => (object) [
                        'type' => 'Type1',
                        'optional' => false,
                    ],
                ]
            );

        try {
            $this->requestEntityLoader->filterIn($request);
            $this->fail();
        } catch (UnexpectedValueException $e) {
            self::assertSame('Parameter "entity" can\'t be null.', $e->getMessage());
        }
    }

    /**
     * @param mixed $value
     *
     * @return Request
     */
    private function createRequest($value): Request
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
