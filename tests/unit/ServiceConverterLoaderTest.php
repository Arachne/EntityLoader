<?php

namespace Tests\Unit;

use Arachne\EntityLoader\ServiceConverterLoader;
use Codeception\TestCase\Test;
use Mockery;
use Mockery\MockInterface;

/**
 * @author Jáchym Toušek
 */
class ServiceConverterLoaderTest extends Test
{

	/** @var ServiceConverterLoader */
	private $converterLoader;

	/** @var MockInterface */
	private $container;

	protected function _before()
	{
		$this->container = Mockery::mock('Nette\DI\Container');
		$this->converterLoader = new ServiceConverterLoader([
			'Type1' => 'type1Converter',
		], $this->container);
	}

	public function testHandler()
	{
		$mock = Mockery::mock('Arachne\EntityLoader\IConverter');
		$this->container
			->shouldReceive('getService')
			->once()
			->with('type1Converter')
			->andReturn($mock);
		$this->assertSame($mock, $this->converterLoader->getConverter('Type1'));
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\UnexpectedTypeException
	 * @expectedExceptionMessage Service 'type1Converter' is not an instance of Arachne\EntityLoader\IConverter.
	 */
	public function testConverterWrongClass()
	{
		$this->container
			->shouldReceive('getService')
			->once()
			->with('type1Converter')
			->andReturn(Mockery::mock());
		$this->converterLoader->getConverter('Type1');
	}

	/**
	 * @expectedException Arachne\EntityLoader\Exception\UnexpectedTypeException
	 * @expectedExceptionMessage No converter found for type 'Type2'.
	 */
	public function testConverterNotFound()
	{
		$this->converterLoader->getConverter('Type2');
	}

}
