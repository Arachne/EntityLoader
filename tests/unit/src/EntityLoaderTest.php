<?php

namespace Tests\Unit;

use Arachne\EntityLoader\EntityLoader;
use Codeception\TestCase\Test;
use Mockery;

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
		$mapping = [
			'entity' => 'Type1',
		];
		$parameters = [
			'entity' => 'value1',
		];
		$entityLoader = new EntityLoader([]);
		$entityLoader->loadEntities($parameters, $mapping);
	}

}
