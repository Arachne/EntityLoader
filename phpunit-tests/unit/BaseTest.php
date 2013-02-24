<?php

namespace ArachneTests\EntityLoader;

use Mockery;

abstract class BaseTest extends \PHPUnit_Framework_TestCase
{

	public function run(\PHPUnit_Framework_TestResult $result = NULL)
	{
		$this->setPreserveGlobalState(FALSE);
		return parent::run($result);
	}

	protected function prepareTemplate(\Text_Template $template)
	{
		parent::prepareTemplate($template);
		$template->setVar([
			'globals' => '$__PHPUNIT_BOOTSTRAP = ' . var_export($GLOBALS['__PHPUNIT_BOOTSTRAP'], TRUE) . ';',
		]);
	}

	protected function tearDown()
	{
		parent::tearDown();
		Mockery::close();
	}

}
