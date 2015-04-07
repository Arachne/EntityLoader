<?php

namespace Tests\Unit;

use Arachne\DIHelpers\ResolverInterface;
use Arachne\EntityLoader\EntityUnloader;
use Arachne\EntityLoader\FilterOutInterface;
use Arachne\EntityLoader\TypeDetectorInterface;
use Codeception\TestCase\Test;
use Mockery;
use Mockery\MockInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityUnloaderTest extends Test
{

	/** @var EntityUnloader */
	private $entityUnloader;

	/** @var MockInterface */
	private $filter;

	/** @var MockInterface */
	private $filterResolver;

	/** @var MockInterface */
	private $typeDetector;

	protected function _before()
	{
		$this->filter = Mockery::mock(FilterOutInterface::class);
		$this->filterResolver = Mockery::mock(ResolverInterface::class);
		$this->typeDetector = Mockery::mock(TypeDetectorInterface::class);
		$this->entityUnloader = new EntityUnloader($this->filterResolver, $this->typeDetector);
	}

	public function testFilterOut()
	{
		$this->filterResolver
			->shouldReceive('resolve')
			->once()
			->with('Type1')
			->andReturn($this->filter);

		$mock1 = Mockery::mock('Type1');

		$this->filter
			->shouldReceive('filterOut')
			->once()
			->with($mock1)
			->andReturn('1');

		$this->typeDetector
			->shouldReceive('detectType')
			->once()
			->with($mock1)
			->andReturn('Type1');

		$this->assertSame('1', $this->entityUnloader->filterOut($mock1));
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\UnexpectedValueException
	 * @expectedExceptionMessage No filter out found for type 'Type1'.
	 */
	public function testFilterNotFound()
	{
		$mock1 = Mockery::mock('Type1');

		$this->filterResolver
			->shouldReceive('resolve')
			->once()
			->with('Type1')
			->andReturn();

		$this->typeDetector
			->shouldReceive('detectType')
			->once()
			->with($mock1)
			->andReturn('Type1');

		$this->entityUnloader->filterOut($mock1);
	}

}
