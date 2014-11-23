<?php

namespace Tests\Integration;

use Codeception\TestCase\Test;
use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Http\Request as HttpRequest;
use Nette\Http\UrlScript;
use Tests\Integration\Classes\Article;

/**
 * @author Jáchym Toušek
 */
class ConverterTest extends Test
{

	/** @var IRouter */
	private $router;

	public function _before()
	{
		parent::_before();
		$this->router = $this->guy->grabService(IRouter::class);
	}

	public function testEntityIn()
	{
		$httpRequest = new HttpRequest($this->createUrlScript('detail/5'));
		$request = $this->router->match($httpRequest);
		$this->assertInstanceOf(Request::class, $request);
		$parameters = $request->getParameters();
		$this->assertArrayHasKey('entity', $parameters);
		$this->assertInstanceOf(Article::class, $parameters['entity']);
		$this->assertSame('5', $parameters['entity']->getValue());
	}

	public function testEntityOut()
	{
		$request = new Request('Article', 'GET', [
			'action' => 'detail',
			'entity' => new Article('7'),
		]);
		$url = $this->router->constructUrl($request, $this->createUrlScript(''));
		$this->assertSame('http://example.com/detail/article-7', $url);
	}

	public function testIntIn()
	{
		$httpRequest = new HttpRequest($this->createUrlScript('int/5'));
		$request = $this->router->match($httpRequest);
		$this->assertInstanceOf(Request::class, $request);
		$parameters = $request->getParameters();
		$this->assertArrayHasKey('parameter', $parameters);
		$this->assertSame(5, $parameters['parameter']);
	}

	public function testIntOut()
	{
		$request = new Request('Article', 'GET', [
			'action' => 'int',
			'parameter' => 1,
		]);
		$url = $this->router->constructUrl($request, $this->createUrlScript(''));
		$this->assertSame('http://example.com/int/1', $url);
	}

	public function testStringIn()
	{
		$httpRequest = new HttpRequest($this->createUrlScript('string/5'));
		$request = $this->router->match($httpRequest);
		$this->assertInstanceOf(Request::class, $request);
		$parameters = $request->getParameters();
		$this->assertArrayHasKey('parameter', $parameters);
		$this->assertSame('5', $parameters['parameter']);
	}

	public function testStringOut()
	{
		$request = new Request('Article', 'GET', [
			'action' => 'string',
			'parameter' => '1',
		]);
		$url = $this->router->constructUrl($request, $this->createUrlScript(''));
		$this->assertSame('http://example.com/string/1', $url);
	}

	public function testFloatIn()
	{
		$httpRequest = new HttpRequest($this->createUrlScript('float/5.1'));
		$request = $this->router->match($httpRequest);
		$this->assertInstanceOf(Request::class, $request);
		$parameters = $request->getParameters();
		$this->assertArrayHasKey('parameter', $parameters);
		$this->assertSame(5.1, $parameters['parameter']);
	}

	public function testFloatOut()
	{
		$request = new Request('Article', 'GET', [
			'action' => 'float',
			'parameter' => 1.3,
		]);
		$url = $this->router->constructUrl($request, $this->createUrlScript(''));
		$this->assertSame('http://example.com/float/1.3', $url);
	}

	public function testBoolIn()
	{
		$httpRequest = new HttpRequest($this->createUrlScript('bool/0'));
		$request = $this->router->match($httpRequest);
		$this->assertInstanceOf(Request::class, $request);
		$parameters = $request->getParameters();
		$this->assertArrayHasKey('parameter', $parameters);
		$this->assertSame(FALSE, $parameters['parameter']);
	}

	public function testBoolOut()
	{
		$request = new Request('Article', 'GET', [
			'action' => 'bool',
			'parameter' => FALSE,
		]);
		$url = $this->router->constructUrl($request, $this->createUrlScript(''));
		$this->assertSame('http://example.com/bool/0', $url);
	}

	public function testMixedIn()
	{
		$httpRequest = new HttpRequest($this->createUrlScript('mixed/5'));
		$request = $this->router->match($httpRequest);
		$this->assertInstanceOf(Request::class, $request);
		$parameters = $request->getParameters();
		$this->assertArrayHasKey('parameter', $parameters);
		$this->assertSame('5', $parameters['parameter']);
	}

	public function testMixedOut()
	{
		$request = new Request('Article', 'GET', [
			'action' => 'mixed',
			'parameter' => 1,
		]);
		$url = $this->router->constructUrl($request, $this->createUrlScript(''));
		$this->assertSame('http://example.com/mixed/1', $url);
	}

	public function testArrayIn()
	{
		$httpRequest = new HttpRequest($this->createUrlScript('array?parameter=1'));
		$request = $this->router->match($httpRequest);
		$this->assertInstanceOf(Request::class, $request);
		$parameters = $request->getParameters();
		$this->assertArrayHasKey('parameter', $parameters);
		$this->assertSame([ '1' ], $parameters['parameter']);
	}

	public function testArrayOut()
	{
		$request = new Request('Article', 'GET', [
			'action' => 'array',
			'parameter' => [ '1' => '2' ],
		]);
		$url = $this->router->constructUrl($request, $this->createUrlScript(''));
		$this->assertSame('http://example.com/array?parameter%5B1%5D=2', $url);
	}

	private function createUrlScript($url, array $params = array())
	{
		$urlScript = new UrlScript('http://example.com/' . $url);
		$urlScript->appendQuery($params);
		return $urlScript;
	}

}
