<?php

declare(strict_types=1);

namespace Tests\Unit;

use Arachne\EntityLoader\EntityUnloader;
use Arachne\EntityLoader\Exception\UnexpectedValueException;
use Arachne\EntityLoader\FilterOutInterface;
use ArrayObject;
use Codeception\Test\Unit;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Phpunit\Phony;

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

    protected function _before(): void
    {
        $this->filterHandle = Phony::mock(FilterOutInterface::class);
        $this->filterIterator = new ArrayObject();
        $this->entityUnloader = new EntityUnloader($this->filterIterator);
    }

    public function testFilterOut(): void
    {
        $this->filterIterator[] = $this->filterHandle->get();

        $stub = Phony::stub();

        $this->filterHandle
            ->supports
            ->returns(true);

        $this->filterHandle
            ->filterOut
            ->with($stub)
            ->returns('1');

        self::assertSame('1', $this->entityUnloader->filterOut($stub));
    }

    public function testFilterNotFound(): void
    {
        try {
            $this->entityUnloader->filterOut(Phony::stub());
            self::fail();
        } catch (UnexpectedValueException $e) {
            self::assertSame('No filter out found for class "Eloquent\Phony\Stub\StubVerifier".', $e->getMessage());
        }
    }
}
