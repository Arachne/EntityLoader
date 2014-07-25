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
use Arachne\EntityLoader\Exception\UnexpectedValueException;
use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class EntityLoader extends Object
{

	/** @var IConverter[] */
	private $converters;

	/** @var IConverter[] */
	private $cachedConverters;

	/**
	 * @param IConverter[] $converters
	 */
	public function __construct(array $converters)
	{
		$this->converters = $converters;
		$this->cachedConverters = array();
	}

	/**
	 * Replaces scalars in array by entities.
	 * @param array $parameters
	 * @param array $mapping	 
	 */
	public function loadEntities(array $parameters, array $mapping)
	{
		foreach ($mapping as $name => $type) {
			if (isset($parameters[$name]) && !$parameters[$name] instanceof $type) {
				$entity = $this->getConverter($type)->parameterToEntity($type, $parameters[$name]);
				if (!$entity instanceof $type) {
					throw new UnexpectedValueException("Converter did not return an instance of '$type'.");
				}
				$parameters[$name] = $entity;
			}
		}
		return $parameters;
	}

	/**
	 * Replaces entities in array by scalars.
	 * @param array $parameters
	 * @param array $mapping	 
	 * @param bool $envelopes
	 */
	public function removeEntities(array $parameters, array $mapping, $envelopes = FALSE)
	{
		foreach ($mapping as $name => $type) {
			if (isset($parameters[$name]) && $parameters[$name] instanceof $type) {
				$parameter = $this->getConverter($type)->entityToParameter($type, $parameters[$name]);
				if (!is_string($parameter)) {
					throw new UnexpectedValueException("Converter for '$type' did not return a string.");
				}
				$parameters[$name] = $envelopes ? new EntityEnvelope($parameters[$name], $parameter) : $parameter;
			}
		}
		return $parameters;
	}

	/**
	 * @param string $type
	 * @return IConverter
	 */
	private function getConverter($type)
	{
		if (!isset($this->cachedConverters[$type])) {
			foreach ($this->converters as $converter) {
				if ($converter->canConvert($type)) {
					$this->cachedConverters[$type] = $converter;
					return $converter;
				}
			}
			throw new UnexpectedTypeException("No converter found for type '$type'.");
		}
		return $this->cachedConverters[$type];
	}

}
