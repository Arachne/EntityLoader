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
	 * @return bool
	 */
	public function loadEntities(Request $request)
	{
		$entities = $this->finder->getEntityParameters($request);
		if (empty($entities)) {
			return $request;
		}
        $parameters = $request->getParameters();
		foreach ($entities as $name => $type) {
			if (isset($parameters[$name])) {
				$converter = $this->converterLoader->getConverter($type);
				$entity = $converter ? $converter->parameterToEntity($type, $parameters[$name]) : NULL;
                if ($entity === NULL) {
					return FALSE;
				}
				$parameters[$name] = $entity;
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
		foreach ($entities as $name => $type) {
			if (isset($parameters[$name])) {
				$converter = $this->converterLoader->getConverter($type);
     	    	$parameter = $converter ? $converter->entityToParameter($type, $parameters[$name]) : NULL;
                if ($parameter === NULL) {
					return FALSE;
				}
				$parameters[$name] = new EntityProxy($parameters[$name], $parameter);
			}
		}
		$request->setParameters($parameters);
		return TRUE;
	}

}
