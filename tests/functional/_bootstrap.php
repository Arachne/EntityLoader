<?php

use Arachne\Bootstrap\Configurator;
use Arachne\Codeception\Module\Nette;

$configurator = new Configurator;
$configurator->enableDebugger(__DIR__ . '/../_log');
$tempDir = __DIR__ . '/../_temp/functional_' . md5(time());
mkdir($tempDir);
$configurator->setTempDirectory($tempDir);
$configurator->setDebugMode(true);

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon');

// Don't use this instance for anything else than console commands!
$container = $configurator->createContainer();
Nette::$containerClass = get_class($container);
return $container;
