<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\Application;

use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class Envelope extends Object
{

	/** @var array|object|resource */
	private $value;

	/** @var string|int */
	private $identifier;

	/**
	 * @param array|object|resource $value
	 * @param string|int $identifier
	 */
	public function __construct($value, $identifier)
	{
		$this->value = $value;
		$this->identifier = $identifier;
	}

	/**
	 * @return array|object|resource
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @return string|int
	 */
	public function getIdentifier()
	{
		return $this->identifier;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->identifier;
	}

}
