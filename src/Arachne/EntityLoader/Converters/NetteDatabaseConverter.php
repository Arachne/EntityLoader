<?php

namespace Arachne\EntityLoader\Converters;

/**
 * @author Jáchym Toušek
 */
class NetteDatabaseConverter extends \Nette\Object implements \Arachne\EntityLoader\IParameterConverter
{

	/** @var \Nette\Database\Connection */
	protected $connection;

	/**
	 * @param \Nette\Database\Connection $connection
	 */
	public function __construct(\Nette\Database\Connection $connection)
	{
		$this->connection = $connection;
	}

	/**
	 * @param mixed $value
	 * @param string $mapping
	 * @return boolean
	 */
	public function parameterToEntity($value, $mapping)
	{
		throw new \Exception("mapping parse");
		//parsování $mapping (?)
		if (strpos($table, '.') !== FALSE) {
			list($table, $column) = explode('.', $table);
		} else {
			$column = NULL;
		}

		$table = $this->connection->table($mapping['table']);
		if ($mapping['column'] === NULL) {
			$entity = $table->get($value);
		} else {
			$entity = $table->where($mapping['column'], $value)->limit(1)->fetch();
		}
		return $entity ?: NULL;
	}

	public function entityToParameter($entity, $mapping)
	{

		// tohle je třeba přepsat
		if (isset($entities[$key]) && $value instanceof \Nette\Database\Table\ActiveRow) {
			$mapping = $entities[$key];
			$table = $value->getTable()->getName();
			if ($table != $mapping['table']) {
				return FALSE;
			}
			if ($mapping['column'] === NULL) {
				$value = $value->getPrimary();
			} else {
				$value = $value->{$mapping['column']};
			}
		}
		if (is_object($value)) {
			return FALSE;
		}

	}

}
