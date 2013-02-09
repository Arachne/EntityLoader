<?php

/**
 * This file is part of the EntityLoader extenstion
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and licence information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

/**
 * @author Jáchym Toušek
 */
class EntityLoader extends \Nette\Object
{

	/** @var \Nette\Database\Connection */
	protected $connection;

	/** @var \Nette\Application\IPresenterFactory */
	protected $presenterFactory;

	/** @var \Nette\Caching\Cache */
	protected $cache;

	/**
	 * @param \Nette\Database\Connection $connection
	 * @param \Nette\Application\IPresenterFactory $presenterFactory
	 * @param \Nette\Caching\IStorage $storage
	 */
	public function __construct(\Nette\Database\Connection $connection, \Nette\Application\IPresenterFactory $presenterFactory, \Nette\Caching\IStorage $storage)
	{
		$this->connection = $connection;
		$this->presenterFactory = $presenterFactory;
		$this->cache = new \Nette\Caching\Cache($storage, 'Arachne.EntityLoader');
	}

	/**
	 * @param \Nette\Reflection\ClassType $reflection
	 * @param string $component
	 * @return \Nette\Application\UI\PresenterComponentReflection|NULL
	 */
	protected function createReflection(\Nette\Reflection\ClassType $reflection, $component)
	{
		$pos = strpos($component, '-');
		if ($pos !== FALSE) {
			$subComponent = substr($component, $pos + 1);
			$component = substr($component, 0, $pos);
		}
		$method = 'createComponent' . ucfirst($component);
		if ($reflection->hasMethod($method)) {
			$element = $reflection->getMethod($method);
			$class = $element->getAnnotation('return');
			if (!$class) {
				return;
			}
			if ($class[0] != '\\') {
				$class = $reflection->getNamespaceName() . '\\' . $class;
			}
			if (class_exists($class)) {
				if (isset($subComponent)) {
					return $this->createReflection(new \Nette\Reflection\ClassType($class), $subComponent);
				} else {
					return new \Nette\Application\UI\PresenterComponentReflection($class);
				}
			} else {
				throw new InvalidStateException("Class '$class' from $reflection->name::$method @return annotation not found.");
			}
		}
	}

	/**
	 * @param \Nette\Reflection\Method $element
	 * @param string $prefix
	 * @param string $default
	 * @return array
	 */
	protected function getMethodEntities(\Nette\Reflection\Method $reflection, $prefix = NULL, $default = NULL)
	{
		$entities = [];
		$annotations = $reflection->getAnnotations();
		if (isset($annotations['Entity'])) {
			foreach ($annotations['Entity'] as $annotation) {
				if (strpos($annotation, ' ') !== FALSE) {
					list($table, $parameter) = explode(' ', $annotation);
					if ($parameter[0] === '$') {
						$parameter = substr($parameter, 1);
					}
					if (strpos($table, '.') !== FALSE) {
						list($table, $column) = explode('.', $table);
					} else {
						$column = NULL;
					}
				} elseif ($default !== NULL) {
					$parameter = $annotation;
					if ($parameter[0] === '$') {
						$parameter = substr($parameter, 1);
					}
					$array = explode(':', $default);
					$table = strtolower(end($array));
					$column = NULL;
				}

				$entities[$prefix . $parameter] = [
					'table' => $table,
					'column' => $column,
				];
			}
		}
		return $entities;
	}

	/**
	 * @param \Nette\Application\UI\PresenterComponentReflection $reflection
	 * @param string
	 * @return array
	 */
	protected function getPersistentEntities(\Nette\Application\UI\PresenterComponentReflection $reflection, $prefix = NULL)
	{
		$entities = [];
		foreach ($reflection->getPersistentParams() as $persistent => $_) {
			$annotations = $reflection->getProperty($persistent)->getAnnotations();
			if (isset($annotations['Entity'])) {
				foreach ($annotations['Entity'] as $annotation) {
					if ($annotation === TRUE) {
						$table = $persistent;
						$column = NULL;
					} else {
						$table = $annotation;
						if (strpos($table, '.') !== FALSE) {
							list($table, $column) = explode('.', $table);
						} else {
							$column = NULL;
						}
					}

					$entities[$prefix . $persistent] = [
						'table' => $table,
						'column' => $column,
					];
				}
			}
		}
		return $entities;
	}

	/**
	 * @param \Nette\Application\Request $request
	 * @return array
	 */
	protected function getCacheKey(\Nette\Application\Request $request)
	{
		$parameters = $request->getParameters();
		$key = [
			'presenter' => $request->getPresenterName(),
			'action' => $parameters[\Nette\Application\UI\Presenter::ACTION_KEY],
		];
		unset($parameters[\Nette\Application\UI\Presenter::ACTION_KEY]);
		if (isset($parameters[\Nette\Application\UI\Presenter::SIGNAL_KEY])) {
			$key['signal'] = $parameters[\Nette\Application\UI\Presenter::SIGNAL_KEY];
			unset($parameters[\Nette\Application\UI\Presenter::SIGNAL_KEY]);
		}
		$key['parameters'] = array_keys($parameters);
		return $key;
	}

	/**
	 * Returns entity parameters based on the request.
	 * @param \Nette\Application\Request $request
	 * @return array
	 */
	protected function getEntityParameters(\Nette\Application\Request $request)
	{
		$cacheKey = $this->getCacheKey($request);
		$entities = $this->cache->load($cacheKey);
		if ($entities !== NULL) {
			return $entities;
		}

		$parameters = $request->getParameters();
		$presenter = $request->getPresenterName();
		$class = $this->presenterFactory->getPresenterClass($presenter);
		$presenterReflection = new \Nette\Application\UI\PresenterComponentReflection($class);
		$files = [];

		// Presenter persistent entities
		$entities = $this->getPersistentEntities($presenterReflection);
		$files[] = $presenterReflection->getFileName();

		// Action entities
		$action = $parameters[\Nette\Application\UI\Presenter::ACTION_KEY];
		$method = 'action' . $action;
		$element = $presenterReflection->hasCallableMethod($method) ? $presenterReflection->getMethod($method) : NULL;
		if (!$element) {
			$method = 'render' . $action;
			$element = $presenterReflection->hasCallableMethod($method) ? $presenterReflection->getMethod($method) : NULL;
		}
		if ($element) {
			$entities += $this->getMethodEntities($element, '', $presenter);
			$files[] = $element->getFileName();
		}

		// Persistent component entities
		$components = [];
		foreach ($parameters as $key => $_) {
			$pos = strrpos($key, '-');
			if ($pos !== FALSE) {
				$component = substr($key, 0, $pos);
				if (!isset($components[$component])) {
					$reflection = $this->createReflection($presenterReflection, $component);
					$components[$component] = TRUE;
					if ($reflection) {
						$files[] = $reflection->getFileName();
						$entities += $this->getPersistentEntities($reflection, $component . '-');
					}
				}
			}
		}

		// Signal entities
		if (isset($parameters[\Nette\Application\UI\Presenter::SIGNAL_KEY])) {
			$signal = $parameters[\Nette\Application\UI\Presenter::SIGNAL_KEY];
			$pos = strrpos($signal, '-');
			if ($pos !== FALSE) {
				$component = substr($signal, 0, $pos);
				$signal = substr($signal, $pos + 1);
				$reflection = $this->createReflection($presenterReflection, $component);
				$prefix = $component . '-';
			} else {
				$reflection = $presenterReflection;
				$prefix = '';
			}
			$method = 'handle' . ucfirst($signal);
			if ($reflection && $reflection->hasCallableMethod($method)) {
				$element = $reflection->getMethod($method);
				$entities += $this->getMethodEntities($element, $prefix);
				$files[] = $element->getFileName();
			}
		}

		//Does not invalidate if a component factory file was changed (see getComponentReflection method)
		$this->cache->save($cacheKey, $entities, [
			\Nette\Caching\Cache::FILES => $files,
		]);

		return $entities;
	}

	/**
	 * Replaces scalars in request by entities.
	 * @param \Nette\Application\Request $request
	 * @return bool
	 */
	public function loadEntities(\Nette\Application\Request $request)
	{
		$entities = $this->getEntityParameters($request);
		if (empty($entities)) {
			return $request;
		}

		$parameters = $request->getParameters();
		foreach ($entities as $key => $mapping) {
			if (isset($parameters[$key])) {
				$table = $this->connection->table($mapping['table']);
				if ($mapping['column'] === NULL) {
					$parameters[$key] = $table->get($parameters[$key]);
				} else {
					$parameters[$key] = $table->where($mapping['column'], $parameters[$key])->limit(1)->fetch();
				}
				if ($parameters[$key] === FALSE) {
					return FALSE;
				}
			}
		}
		$request->setParameters($parameters);
		return TRUE;
	}

	/**
	 * Replaces entities in request by scalars.
	 * @param \Nette\Application\Request $request
	 * @return bool
	 */
	public function removeEntities(\Nette\Application\Request $request)
	{
		$entities = $this->getEntityParameters($request);
		if (empty($entities)) {
			return $request;
		}

		$parameters = $request->getParameters();
		foreach ($parameters as $key => &$value) {
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
		$request->setParameters($parameters);
		return TRUE;
	}

}
