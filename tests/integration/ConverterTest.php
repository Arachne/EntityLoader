<?php

namespace Tests\Integration;

use Tests\Integration\Article;

class ConverterTest extends BaseTest
{

	/** @var \Nette\Application\IRouter */
	private $router;

	public function _before()
	{
		parent::_before();
		$this->router = $this->codeGuy->grabService('Nette\Application\IRouter');
	}

	public function testRouterIn()
	{
		$httpRequest = new \Nette\Http\Request($this->createUrlScript('5'));
		$request = $this->router->match($httpRequest);
		$this->assertInstanceOf('Nette\Application\Request', $request);
		$parameters = $request->getParameters();
		$this->assertArrayHasKey('entity', $parameters);
		$this->assertInstanceOf('Tests\Integration\Article', $parameters['entity']);
		$this->assertSame('5', $parameters['entity']->getValue());
	}

	public function testRouterOut()
	{
		$request = new \Nette\Application\Request('Article', 'GET', [
			'action' => 'detail',
			'entity' => new Article('7'),
		]);
		$url = $this->router->constructUrl($request, $this->createUrlScript(''));
		$this->assertSame('http://example.com/article-7', $url);
	}

	private function createUrlScript($url, array $params = array())
	{
		$urlScript = new \Nette\Http\UrlScript('http://example.com/' . $url);
		$urlScript->appendQuery($params);
		return $urlScript;
	}

}
