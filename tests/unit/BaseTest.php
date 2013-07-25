<?php

namespace Tests\Arachne\EntityLoader;

abstract class BaseTest extends \Codeception\TestCase\Test
{

	protected function tearDown()
	{
		\Mockery::close();
		parent::tearDown();
	}

}
