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
		$this->router = $this->guy->grabService('Nette\Application\IRouter');
	}

	public function testRouterIn()
	{
		$httpRequest = new HttpRequest($this->createUrlScript('5'));
		$request = $this->router->match($httpRequest);
		$this->assertInstanceOf('Nette\Application\Request', $request);
		$parameters = $request->getParameters();
		$this->assertArrayHasKey('entity', $parameters);
		$this->assertInstanceOf('Tests\Integration\Classes\Article', $parameters['entity']);
		$this->assertSame('5', $parameters['entity']->getValue());
	}

	public function testRouterOut()
	{
		$request = new Request('Article', 'GET', [
			'action' => 'detail',
			'entity' => new Article('7'),
		]);
		$url = $this->router->constructUrl($request, $this->createUrlScript(''));
		$this->assertSame('http://example.com/article-7', $url);
	}

	private function createUrlScript($url, array $params = array())
	{
		$urlScript = new UrlScript('http://example.com/' . $url);
		$urlScript->appendQuery($params);
		return $urlScript;
	}

}
