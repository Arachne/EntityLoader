<?php

namespace Arachne\EntityLoader;

/**
 * @author Jáchym Toušek
 */
interface IParameterConverter
{

	/**
	 * @param mixed $value
	 * @param string $mapping
	 * @return mixed
	 */
	public function parameterToEntity($value, $mapping);

	/**
	 * @param mixed $entity
	 * @param string $mapping
	 * @return mixed
	 */
	public function entityToParameter($entity, $mapping);

}
