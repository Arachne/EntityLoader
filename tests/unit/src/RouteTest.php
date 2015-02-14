<?php

namespace Tests\Unit;

use Arachne\EntityLoader\Application\Envelope;
use Arachne\EntityLoader\Application\Route;
use Codeception\TestCase\Test;
use Mockery;
use Nette\Application\Request;
use Nette\Http\Url;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RouteTest extends Test
{

	public function testNoGlobalFilterOut()
	{
		$route = new Route('', [
			'presenter' => 'Test',
			'param1' => [
				Route::FILTER_OUT => function (Envelope $envelope) {
					return $envelope->getObject();
				},
			],
		]);

		$request = new Request('Test', 'GET', [
			'param1' => new Envelope('param1_value', 'param1_id'),
			'param2' => new Envelope('param2_value', 'param2_id'),
		]);

		$url = new Url('/');
		$this->assertSame('http:///?param1=param1_value&param2=param2_id', $route->constructUrl($request, $url));
	}

	public function testGlobalFilterOut()
	{
		$mock = Mockery::mock();
		$mock->shouldReceive('call')
			->once()
			->with(Mockery::on(function ($parameters) {
				$this->assertInstanceOf(Envelope::class, $parameters['param1']);
				$this->assertInstanceOf(Envelope::class, $parameters['param2']);
				return TRUE;
			}))
			->andReturnUsing(function ($parameters) {
				return $parameters;
			});

		$route = new Route('', [
			'presenter' => 'Test',
			'param1' => [
				Route::FILTER_OUT => function (Envelope $envelope) {
					return $envelope->getObject();
				},
			],
			NULL => [
				Route::FILTER_OUT => function ($params) use ($mock) {
					return $mock->call($params);
				},
			],
		]);

		$request = new Request('Test', 'GET', [
			'param1' => new Envelope('param1_value', 'param1_id'),
			'param2' => new Envelope('param2_value', 'param2_id'),
		]);

		$url = new Url('/');
		$this->assertSame('http:///?param1=param1_value&param2=param2_id', $route->constructUrl($request, $url));
	}

}
