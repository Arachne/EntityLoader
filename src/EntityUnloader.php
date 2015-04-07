<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

use Arachne\DIHelpers\ResolverInterface;
use Arachne\EntityLoader\Exception\UnexpectedValueException;
use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityUnloader extends Object
{

	/** @var ResolverInterface */
	private $filterOutResolver;

	/** @var TypeDetectorInterface */
	private $typeDetector;

	/**
	 * @param ResolverInterface $filterOutResolver
	 * @param TypeDetectorInterface $typeDetector
	 */
	public function __construct(ResolverInterface $filterOutResolver, TypeDetectorInterface $typeDetector)
	{
		$this->filterOutResolver = $filterOutResolver;
		$this->typeDetector = $typeDetector;
	}

	/**
	 * @param object $object
	 * @return string|array
	 */
	public function filterOut($object)
	{
		$type = $this->typeDetector->detectType($object);
		return $this->getFilter($type)->filterOut($object);
	}

	/**
	 * @param string $type
	 * @return FilterOutInterface
	 */
	private function getFilter($type)
	{
		$filter = $this->filterOutResolver->resolve($type);
		if (!$filter) {
			throw new UnexpectedValueException("No filter out found for type '$type'.");
		}
		return $filter;
	}

}
