<?php

/**
 * This file is part of the Arachne EntityLoader extenstion
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class EntityEnvelope extends Object
{

	/** @var object */
	private $entity;

	/** @var string */
	private $identifier;

	/**
	 * @param object $entity
	 * @param callable $identifier
	 */
	public function __construct($entity, $identifier)
	{
		$this->entity = $entity;
		$this->identifier = $identifier;
	}

	public function getEntity()
	{
		return $this->entity;
	}

	public function __toString()
	{
		return $this->identifier;
	}

}
