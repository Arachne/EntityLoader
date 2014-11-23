<?php

namespace Tests\Unit;

use Arachne\EntityLoader\EntityLoader;
use Arachne\EntityLoader\ConverterInterface;
use Codeception\TestCase\Test;
use Mockery;
use Mockery\MockInterface;

/**
 * @author Jáchym Toušek
 */
class EntityLoaderFilterOutTest extends Test
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
		$this->converterResolver = Mockery::mock();
		$resolver = function ($name) {
			return $this->converterResolver->resolve($name);
		};
		$this->entityLoader = new EntityLoader($resolver);
	}

	public function testFilterOut()
	{
		$this->converterResolver
			->shouldReceive('resolve')
			->once()
			->with('Type1')
			->andReturn($this->converter);

		$mock1 = Mockery::mock('Type1');

		$this->converter
			->shouldReceive('filterOut')
			->once()
			->with('Type1', $mock1)
			->andReturn('1');

		$this->assertSame('1', $this->entityLoader->filterOut('Type1', $mock1));
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\UnexpectedValueException
	 * @expectedExceptionMessage Converter for 'Type1' did not return a string nor an array.
	 */
	public function testFilterOutFail()
	{
		$this->converterResolver
			->shouldReceive('resolve')
			->once()
			->with('Type1')
			->andReturn($this->converter);

		$mock1 = Mockery::mock('Type1');

		$this->converter
			->shouldReceive('filterOut')
			->once()
			->with('Type1', $mock1)
			->andReturn(NULL);

		$this->entityLoader->filterOut('Type1', $mock1);
	}

	public function testFilterOutIgnore()
	{
		$this->assertSame('1', $this->entityLoader->filterOut('Type1', '1'));
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\UnexpectedValueException
	 * @expectedExceptionMessage Invalid parameter value for type 'Type1'.
	 */
	public function testFilterOutInvalid()
	{
		$this->entityLoader->filterOut('Type1', 1);
	}

}
