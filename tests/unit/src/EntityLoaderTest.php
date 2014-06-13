<?php

namespace Tests\Unit;

use Arachne\EntityLoader\EntityLoader;
use Arachne\EntityLoader\ParameterFinder;
use Codeception\TestCase\Test;
use Mockery;
use Nette\Application\Request;

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
		$finder = Mockery::mock(ParameterFinder::class);
		$finder->shouldReceive('getEntityParameters')
			->once()
			->andReturn([
				'entity' => 'Type1',
			]);
		$request = new Request('', 'GET', [
			'entity' => 'value1',
		]);
		$entityLoader = new EntityLoader([], $finder);
		$entityLoader->loadEntities($request);
	}

}
