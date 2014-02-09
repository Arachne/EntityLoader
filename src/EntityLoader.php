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
use Nette\Application\Request;
use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class EntityLoader extends Object
{

	/** @var ParameterFinder */
	private $finder;

	/** @var IConverter[] */
	private $converters;

	/** @var IConverter[] */
	private $cachedConverters;

	/**
	 * @param IConverter[] $converters
	 * @param ParameterFinder $finder
	 */
	public function __construct(array $converters, ParameterFinder $finder)
	{
		$this->finder = $finder;
		$this->converters = $converters;
		$this->cachedConverters = array();
	}

	/**
	 * Replaces scalars in request by entities.
	 * @param Request $request
	 */
	public function loadEntities(Request $request)
	{
		$entities = $this->finder->getEntityParameters($request);
		if (empty($entities)) {
			return;
		}
		$parameters = $request->getParameters();
		foreach ($entities as $name => $type) {
			if (isset($parameters[$name]) && !$parameters[$name] instanceof $type) {
				$entity = $this->getConverter($type)->parameterToEntity($type, $parameters[$name]);
				if (!$entity instanceof $type) {
					throw new UnexpectedValueException("Converter did not return an instance of '$type'.");
				}
				$parameters[$name] = $entity;
			}
		}
		$request->setParameters($parameters);
	}

	/**
	 * Replaces entities in request by scalars.
	 * @param Request $request
	 * @param bool $envelopes
	 */
	public function removeEntities(Request $request, $envelopes = FALSE)
	{
		$entities = $this->finder->getEntityParameters($request);
		if (empty($entities)) {
			return;
		}
		$parameters = $request->getParameters();
		foreach ($entities as $name => $type) {
			if (isset($parameters[$name]) && $parameters[$name] instanceof $type) {
				$parameter = $this->getConverter($type)->entityToParameter($type, $parameters[$name]);
				if (!is_string($parameter)) {
					throw new UnexpectedValueException("Converter for '$type' did not return a string.");
				}
				$parameters[$name] = $envelopes ? new EntityEnvelope($parameters[$name], $parameter) : $parameter;
			}
		}
		$request->setParameters($parameters);
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
