<?php

namespace Tests\Unit;

use Arachne\DIHelpers\ResolverInterface;
use Arachne\EntityLoader\EntityLoader;
use Codeception\TestCase\Test;
use Mockery;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityLoaderTest extends Test
{

	/**
	 * @expectedException Arachne\EntityLoader\Exception\UnexpectedTypeException
	 * @expectedExceptionMessage No converter found for type 'Type1'.
	 */
	public function testConverterNotFound()
	{
		$parameters = [
			'entity' => 'value1',
		];

		$resolver = Mockery::mock(ResolverInterface::class);
		$resolver->shouldReceive('resolve')
			->once()
			->with('Type1')
			->andReturn();

		$entityLoader = new EntityLoader($resolver);
		$entityLoader->filterIn('Type1', $parameters);
	}

}
