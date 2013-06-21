<?php

use Nette\Config\Configurator;

// Composer autoloading
require __DIR__ . '/../../vendor/autoload.php';

// Configure application
$configurator = new Configurator();

// Enable RobotLoader
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();
