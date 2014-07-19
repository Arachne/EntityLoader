<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\Application;

use Arachne\EntityLoader\EntityLoader;
use Nette\Application\Request;
use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class RequestEntityLoader extends Object
{

	/** @var EntityLoader */
	private $entityLoader;

	/** @var ParameterFinder */
	private $finder;

	/**
	 * @param EntityLoader $entityLoader
	 * @param ParameterFinder $finder
	 */
	public function __construct(EntityLoader $entityLoader, ParameterFinder $finder)
	{
		$this->entityLoader = $entityLoader;
		$this->finder = $finder;
	}

	/**
	 * Replaces scalars in request by entities.
	 * @param Request $request
	 */
	public function loadEntities(Request $request)
	{
		$mapping = $this->finder->getMapping($request);
		if (empty($mapping)) {
			return;
		}
		$parameters = $this->entityLoader->loadEntities($request->getParameters(), $mapping);
		$request->setParameters($parameters);
	}

	/**
	 * Replaces entities in request by scalars.
	 * @param Request $request
	 * @param bool $envelopes
	 */
	public function removeEntities(Request $request, $envelopes = FALSE)
	{
		$mapping = $this->finder->getMapping($request);
		if (empty($mapping)) {
			return;
		}
		$parameters = $this->entityLoader->removeEntities($request->getParameters(), $mapping, $envelopes);
		$request->setParameters($parameters);
	}

}
