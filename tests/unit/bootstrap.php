<?php

use Nette\Config\Configurator;

define('TEST_DIR', __DIR__ . '/..');

// Composer autoloading
require TEST_DIR . '/../vendor/autoload.php';

// Configure application
$configurator = new Configurator();

// Enable Nette Debugger for error visualisation & logging
$configurator->setDebugMode(Configurator::AUTO);
$configurator->enableDebugger(TEST_DIR . '/log', 'enumag@gmail.com');

// Enable RobotLoader
$configurator->setTempDirectory(TEST_DIR . '/temp');
$configurator->createRobotLoader()
	->addDirectory(TEST_DIR)
	->register();
