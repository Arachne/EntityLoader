<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

use Arachne\EntityLoader\Exception\UnexpectedTypeException;
use Nette\DI\Container;
use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class ServiceConverterLoader extends Object implements IConverterLoader
{

	/** @var Container */
	private $container;

	/** @var string[] */
	private $services;

	/** @var IConverter[] */
	private $converters;

	public function __construct($services, Container $container)
	{
		$this->services = $services;
		$this->container = $container;
	}

	/**
	 * @param string $type
	 * @return IConverter
	 */
	public function getConverter($type)
	{
		if (!isset($this->services[$type])) {
			throw new UnexpectedTypeException("No converter found for type '$type'.");
		}
		$name = $this->services[$type];
		if (!isset($this->converters[$name])) {
			$service = $this->container->getService($name);
			if (!$service instanceof IConverter) {
				throw new UnexpectedTypeException("Service '$name' is not an instance of Arachne\EntityLoader\IConverter.");
			}
			$this->converters[$name] = $service;
		}
		return $this->converters[$name];
	}

}
