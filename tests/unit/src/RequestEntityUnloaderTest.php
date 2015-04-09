<?php

namespace Tests\Unit;

use Arachne\EntityLoader\Application\Envelope;
use Arachne\EntityLoader\Application\RequestEntityUnloader;
use Arachne\EntityLoader\EntityUnloader;
use Codeception\TestCase\Test;
use Mockery;
use Mockery\MockInterface;
use Nette\Application\Request;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityUnloaderTest extends Test
{

	/** @var RequestEntityUnloader */
	private $requestEntityUnloader;

	/** @var MockInterface */
	private $entityUnloader;

	protected function _before()
	{
		$this->entityUnloader = Mockery::mock(EntityUnloader::class);
		$this->requestEntityUnloader = new RequestEntityUnloader($this->entityUnloader);
	}

	public function testFilterOut()
	{
		$mock = Mockery::mock();
		$expected = [
			'entity' => 'value',
		];
		$request = new Request('', 'GET', [
			'entity' => $mock,
		]);
		$this->entityUnloader
			->shouldReceive('filterOut')
			->once()
			->with($mock)
			->andReturn('value');
		$this->requestEntityUnloader->filterOut($request);
		$this->assertEquals($expected, $request->getParameters());
	}

	public function testFilterOutEmptyMapping()
	{
		$expected = [
			'entity' => 'value',
		];
		$request = new Request('', 'GET', $expected);
		$this->requestEntityUnloader->filterOut($request);
		$this->assertEquals($expected, $request->getParameters());
	}

	public function testFilterOutEnvelopes()
	{
		$mock = Mockery::mock();
		$expected = [
			'entity' => new Envelope($mock, 'value'),
		];
		$request = new Request('', 'GET', [
			'entity' => $mock,
		]);
		$this->entityUnloader
			->shouldReceive('filterOut')
			->once()
			->with($mock)
			->andReturn('value');
		$this->requestEntityUnloader->filterOut($request, true);
		$this->assertEquals($expected, $request->getParameters());
	}

	public function testFilterOutNullable()
	{
		$expected = [
			'entity' => null,
		];
		$request = new Request('', 'GET', [
			'entity' => null,
		]);
		$this->requestEntityUnloader->filterOut($request);
		$this->assertEquals($expected, $request->getParameters());
	}

	public function testFilterOutNullableIgnored()
	{
		$expected = [
			'entity' => null,
		];
		$request = new Request('', 'GET', [
			'entity' => null,
		]);
		$this->requestEntityUnloader->filterOut($request);
		$this->assertEquals($expected, $request->getParameters());
	}

}
