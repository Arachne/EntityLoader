<?php

namespace Tests\Unit;

use Arachne\EntityLoader\EntityLoader;
use Codeception\TestCase\Test;

/**
 * @author Jáchym Toušek
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
		$entityLoader = new EntityLoader(function () {
			return NULL;
		});
		$entityLoader->filterIn('Type1', $parameters);
	}

}
