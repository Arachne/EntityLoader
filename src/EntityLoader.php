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
use Arachne\EntityLoader\Exception\UnexpectedTypeException;
use Arachne\EntityLoader\Exception\UnexpectedValueException;
use Nette\Object;
use Nette\Utils\Callback;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityLoader extends Object
{

	/** @var ResolverInterface */
	private $converterResolver;

	/**
	 * @param ResolverInterface $converterResolver
	 */
	public function __construct(ResolverInterface $converterResolver)
	{
		$this->converterResolver = $converterResolver;
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
		$value = $this->getConverter($type)->filterIn($type, $parameter);
		if (!$this->isType($type, $value)) {
			throw new UnexpectedValueException("Converter did not return an instance of '$type'.");
		}
		return $value;
	}

	/**
	 * @param string $type
	 * @param mixed $parameter
	 * @return mixed
	 */
	public function filterOut($type, $parameter)
	{
		if (!$this->isType($type, $parameter)) {
			if (!is_string($parameter) && !is_array($parameter)) {
				throw new UnexpectedValueException("Invalid parameter value for type '$type'.");
			}
			return $parameter;
		}
		$value = $this->getConverter($type)->filterOut($type, $parameter);
		if (!is_string($value) && !is_array($value)) {
			throw new UnexpectedValueException("Converter for '$type' did not return a string nor an array.");
		}
		return $value;
	}

	/**
	 * @param string $type
	 * @return ConverterInterface
	 */
	private function getConverter($type)
	{
		$converter = $this->converterResolver->resolve($type);
		if (!$converter) {
			throw new UnexpectedTypeException("No converter found for type '$type'.");
		}
		return $converter;
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
				return TRUE;
			default:
				return $value instanceof $type;
		}
	}

}
