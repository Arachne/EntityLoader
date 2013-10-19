<?php

/**
 * This file is part of the Arachne EntityLoader extenstion
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

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

	/** @var IConverterLoader */
	private $converterLoader;

	public function __construct(ParameterFinder $finder, IConverterLoader $converterLoader)
	{
		$this->finder = $finder;
		$this->converterLoader = $converterLoader;
	}

	/**
	 * Replaces scalars in request by entities.
	 * @param Request $request
	 */
	public function loadEntities(Request $request)
	{
		$entities = $this->finder->getEntityParameters($request);
		if (empty($entities)) {
			return $request;
		}
		$parameters = $request->getParameters();
		foreach ($entities as $name => $type) {
			if (isset($parameters[$name]) && !$parameters[$name] instanceof $type) {
				$converter = $this->converterLoader->getConverter($type);
				$entity = $converter->parameterToEntity($type, $parameters[$name]);
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
			return $request;
		}
		$parameters = $request->getParameters();
		foreach ($entities as $name => $type) {
			if (isset($parameters[$name]) && $parameters[$name] instanceof $type) {
				$converter = $this->converterLoader->getConverter($type);
				$parameter = $converter->entityToParameter($type, $parameters[$name]);
				if (!is_string($parameter)) {
					throw new UnexpectedValueException("Converter for '$type' did not return a string.");
				}
				$parameters[$name] = $envelopes ? new EntityEnvelope($parameters[$name], $parameter) : $parameter;
			}
		}
		$request->setParameters($parameters);
	}

}
