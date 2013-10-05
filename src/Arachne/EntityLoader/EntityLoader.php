<?php

/**
 * This file is part of the Arachne EntityLoader extenstion
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

use Nette\Application\Request;
use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class EntityLoader extends Object
{

	/** @var \Arachne\EntityLoader\ParameterFinder */
	protected $finder;

	/** @var \Arachne\EntityLoader\IConverter */
	protected $converter;

	/**
	 * @param \Arachne\EntityLoader\ParameterFinder $finder
	 * @param \Arachne\EntityLoader\IConverter $converter
	 */
	public function __construct(ParameterFinder $finder, IConverter $converter)
	{
		$this->finder = $finder;
		$this->converter = $converter;
	}

	/**
	 * Replaces scalars in request by entities.
	 * @param Request $request
	 * @return bool
	 */
	public function loadEntities(Request $request)
	{
		$entities = $this->finder->getEntityParameters($request);
		if (empty($entities)) {
			return $request;
		}

		$parameters = $request->getParameters();
		foreach ($entities as $key => $annotation) {
			if (isset($parameters[$key])) {
				$entity = $this->converter->parameterToEntity($annotation, $parameters[$key]);
				if ($entity === NULL) {
					return FALSE;
				}
				$parameters[$key] = $entity;
			}
		}
		$request->setParameters($parameters);
		return TRUE;
	}

	/**
	 * Replaces entities in request by scalars.
	 * @param Request $request
	 * @return bool
	 */
	public function removeEntities(Request $request)
	{
		$entities = $this->finder->getEntityParameters($request);
		if (empty($entities)) {
			return $request;
		}

		$parameters = $request->getParameters();
		foreach ($entities as $key => $annotation) {
			if (isset($parameters[$key])) {
				$parameter = $this->converter->entityToParameter($annotation, $parameters[$key]);
				if ($parameter === NULL) {
					return FALSE;
				}
				$parameters[$key] = $parameter;
			}
		}
		$request->setParameters($parameters);
		return TRUE;
	}

}
