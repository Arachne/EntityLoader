<?php

namespace Tests\Unit;

use Codeception\TestCase\Test;
use Mockery;

abstract class BaseTest extends Test
{

	protected function tearDown()
	{
		Mockery::close();
		parent::tearDown();
	}

}
