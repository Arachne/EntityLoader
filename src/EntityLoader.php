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
class EntityLoader extends Object
{

	/** @var ResolverInterface */
	private $filterInResolver;

	/**
	 * @param ResolverInterface $filterInResolver
	 */
	public function __construct(ResolverInterface $filterInResolver)
	{
		$this->filterInResolver = $filterInResolver;
	}

	/**
	 * @param string $type
	 * @param mixed $parameter
	 * @return mixed
	 */
	public function filterIn($type, $parameter)
	{
		if ($this->isType($type, $parameter)) {
			return $parameter;
		}
		$value = $this->getfilter($type)->filterIn($parameter);
		if (!$this->isType($type, $value)) {
			throw new UnexpectedValueException("FilterIn did not return an instance of '$type'.");
		}
		return $value;
	}

	/**
	 * @param string $type
	 * @return filterInInterface
	 */
	private function getfilter($type)
	{
		$filter = $this->filterInResolver->resolve($type);
		if (!$filter) {
			throw new UnexpectedValueException("No filter in found for type '$type'.");
		}
		return $filter;
	}

	/**
	 * @param string $type
	 * @param mixed $value
	 * @return bool
	 */
	private function isType($type, $value)
	{
		switch ($type) {
			case 'int':
				return is_int($value);
			case 'float':
				return is_float($value);
			case 'bool':
				return is_bool($value);
			case 'string':
				return is_string($value);
			case 'array':
				return is_array($value);
			case 'object':
				return is_object($value);
			case 'resource':
				return is_resource($value);
			case 'callable':
				return is_callable($value);
			case 'mixed':
				return true;
			default:
				return $value instanceof $type;
		}
	}

}
