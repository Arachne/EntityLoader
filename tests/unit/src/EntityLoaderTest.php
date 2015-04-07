<?php

namespace Tests\Unit;

use Arachne\DIHelpers\ResolverInterface;
use Arachne\EntityLoader\EntityLoader;
use Arachne\EntityLoader\FilterInInterface;
use Codeception\TestCase\Test;
use Mockery;
use Mockery\MockInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityLoaderTest extends Test
{

	/** @var EntityLoader */
	private $entityLoader;

	/** @var MockInterface */
	private $filter;

	/** @var MockInterface */
	private $filterResolver;

	protected function _before()
	{
		$this->filter = Mockery::mock(FilterInInterface::class);
		$this->filterResolver = Mockery::mock(ResolverInterface::class);
		$this->entityLoader = new EntityLoader($this->filterResolver);
	}

	public function testFilterIn()
	{
		$this->filterResolver
			->shouldReceive('resolve')
			->once()
			->with('Type1')
			->andReturn($this->filter);

		$mock1 = Mockery::mock('Type1');

		$this->filter
			->shouldReceive('filterIn')
			->once()
			->with('Type1', 1)
			->andReturn($mock1);

		$this->assertSame($mock1, $this->entityLoader->filterIn('Type1', 1));
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\UnexpectedValueException
	 * @expectedExceptionMessage FilterIn did not return an instance of 'Type1'.
	 */
	public function testFilterInFail()
	{
		$this->filterResolver
			->shouldReceive('resolve')
			->once()
			->with('Type1')
			->andReturn($this->filter);

		$this->filter
			->shouldReceive('filterIn')
			->once()
			->with('Type1', 1)
			->andReturn(NULL);

		$this->entityLoader->filterIn('Type1', 1);
	}

	/**
	 * Make sure that the converter is not called at all if the parameter already has the desired type.
	 */
	public function testFilterInIgnore()
	{
		$mock1 = Mockery::mock('Type1');
		$this->assertSame($mock1, $this->entityLoader->filterIn('Type1', $mock1));
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\UnexpectedValueException
	 * @expectedExceptionMessage No filter in found for type 'Type1'.
	 */
	public function testFilterNotFound()
	{
		$parameters = [
			'entity' => 'value1',
		];

		$this->filterResolver
			->shouldReceive('resolve')
			->once()
			->with('Type1')
			->andReturn();

		$this->entityLoader->filterIn('Type1', $parameters);
	}

}
