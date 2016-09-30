<?php

namespace Tests\Unit;

use Arachne\EntityLoader\Application\Envelope;
use Arachne\EntityLoader\Routing\Route;
use Codeception\Test\Unit;
use Eloquent\Phony\Phpunit\Phony;
use Nette\Application\Request;
use Nette\Http\Url;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RouteTest extends Unit
{
    public function testNoGlobalFilterOut()
    {
        $route = new Route(
            '',
            [
                'presenter' => 'Test',
                'param1' => [
                    Route::FILTER_OUT => function (Envelope $envelope) {
                        return $envelope->getObject();
                    },
                ],
            ]
        );

        $request = new Request(
            'Test',
            'GET',
            [
                'param1' => new Envelope('param1_value', 'param1_id'),
                'param2' => new Envelope('param2_value', 'param2_id'),
            ]
        );

        $url = new Url('/');
        $url->setScheme('http');
        $this->assertSame('http:///?param1=param1_value&param2=param2_id', $route->constructUrl($request, $url));
    }

    public function testGlobalFilterOut()
    {
        $stub = Phony::stub();
        $stub->returnsArgument();

        $route = new Route(
            '',
            [
                'presenter' => 'Test',
                'param1' => [
                    Route::FILTER_OUT => function (Envelope $envelope) {
                        return $envelope->getObject();
                    },
                ],
                null => [
                    Route::FILTER_OUT => $stub,
                ],
            ]
        );

        $request = new Request(
            'Test',
            'GET',
            [
                'param1' => new Envelope('param1_value', 'param1_id'),
                'param2' => new Envelope('param2_value', 'param2_id'),
            ]
        );

        $url = new Url('/');
        $url->setScheme('http');
        $this->assertSame('http:///?param1=param1_value&param2=param2_id', $route->constructUrl($request, $url));

        $parameters = $stub->firstCall()->argument();
        self::assertInstanceOf(Envelope::class, $parameters['param1']);
        self::assertInstanceOf(Envelope::class, $parameters['param2']);
    }
}
