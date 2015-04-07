<?php

namespace Tests\Integration;

use Nette\Application\Application;
use Tests\Integration\Classes\Article;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityUnloaderTest extends Test
{

	public function testLink()
	{
		$this->guy->amOnPage('/default');
		$presenter = $this->guy->grabService(Application::class)->getPresenter();
		$this->assertSame('/entity/5', $presenter->link('Article:entity', new Article(5)));
	}

}
