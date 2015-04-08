<?php

namespace Tests\Integration;

use Arachne\Codeception\ConfigFilesInterface;
use Codeception\TestCase\Test as BaseTest;
use Nette\Bridges\Framework\NetteExtension;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
abstract class Test extends BaseTest implements ConfigFilesInterface
{

	public function getConfigFiles()
	{
		return [
			'config/config.neon' => class_exists(NetteExtension::class) ? 'nette_2.2' : 'nette_2.3',
		];
	}

}
