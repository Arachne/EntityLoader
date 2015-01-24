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
use Arachne\EntityLoader\Exception\UnexpectedValueException;
use Nette\Application\Request;
use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
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
	 * @param Request $request
	 */
	public function filterIn(Request $request)
	{
		$mapping = $this->finder->getMapping($request);
		$parameters = $request->getParameters();
		foreach ($mapping as $name => $info) {
			if (!isset($parameters[$name])) {
				if ($info->nullable) {
					continue;
				} else {
					throw new UnexpectedValueException("Parameter '$name' can't be null.");
				}
			}
			$parameters[$name] = $this->entityLoader->filterIn($info->type, $parameters[$name]);
		}
		$request->setParameters($parameters);
	}

	/**
	 * @param Request $request
	 * @param bool $envelopes
	 */
	public function filterOut(Request $request, $envelopes = FALSE)
	{
		$mapping = $this->finder->getMapping($request);
		$parameters = $request->getParameters();
		foreach ($mapping as $name => $info) {
			if (!isset($parameters[$name])) {
				continue;
			}
			$parameter = $this->entityLoader->filterOut($info->type, $parameters[$name]);
			$parameters[$name] = $envelopes && is_object($parameters[$name]) ? new Envelope($parameters[$name], $parameter) : $parameter;
		}
		$request->setParameters($parameters);
	}

}
