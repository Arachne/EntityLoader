<?php

/**
 * This file is part of the Arachne EntityLoader extenstion
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

use Arachne\EntityLoader\DI\EntityLoaderExtension;
use Nette\DI\Container;
use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class DIConverterLoader extends Object implements IConverterLoader
{

	/** @var Container */
	private $container;

	/** @var string[] */
	private $services;

	/** @var IConverter[] */
	private $converters;

	public function __construct(Container $container)
	{
		$services = [];
		foreach ($container->findByTag(EntityLoaderExtension::TAG_CONVERTER) as $name => $types) {
			foreach ((array) $types as $type) {
				$services[$type] = $name;
			}
		}
		$this->services = $services;
		$this->container = $container;
	}

	/**
	 * @param string $type
	 * @return IConverter|NULL
	 */
	public function getConverter($type)
	{
		if (!isset($this->services[$type])) {
			return NULL;
		}
		$name = $this->services[$type];
		if (!isset($this->converters[$name])) {
			$service = $this->container->getService($name);
			if (!$service instanceof IConverter) {
				throw new InvalidStateException("Service '$name' is not an instance of Arachne\EntityLoader\IConverter.");
			}
			$this->converters[$name] = $service;
		}
		return $this->converters[$name];
	}

}
