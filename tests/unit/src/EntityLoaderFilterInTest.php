<?php

namespace Tests\Unit;

use Arachne\DIHelpers\ResolverInterface;
use Arachne\EntityLoader\EntityLoader;
use Arachne\EntityLoader\ConverterInterface;
use Codeception\TestCase\Test;
use Mockery;
use Mockery\MockInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityLoaderFilterInTest extends Test
{

	/** @var EntityLoader */
	private $entityLoader;

	/** @var MockInterface */
	private $converter;

	/** @var MockInterface */
	private $converterResolver;

	protected function _before()
	{
		$this->converter = Mockery::mock(ConverterInterface::class);
		$this->converterResolver = Mockery::mock(ResolverInterface::class);
		$this->entityLoader = new EntityLoader($this->converterResolver);
	}

	public function testFilterIn()
	{
		$this->converterResolver
			->shouldReceive('resolve')
			->once()
			->with('Type1')
			->andReturn($this->converter);

		$mock1 = Mockery::mock('Type1');

		$this->converter
			->shouldReceive('filterIn')
			->once()
			->with('Type1', 1)
			->andReturn($mock1);

		$this->assertSame($mock1, $this->entityLoader->filterIn('Type1', 1));
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\UnexpectedValueException
	 * @expectedExceptionMessage Converter did not return an instance of 'Type1'.
	 */
	public function testFilterInFail()
	{
		$this->converterResolver
			->shouldReceive('resolve')
			->once()
			->with('Type1')
			->andReturn($this->converter);

		$this->converter
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

}
