<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

use Arachne\EntityLoader\Exception\ClassNotFoundException;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\PresenterComponentReflection;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Object;
use Nette\Reflection\AnnotationsParser;
use Nette\Reflection\ClassType;
use Nette\Reflection\Method;
use Nette\Reflection\Property;
use Nette\Utils\Strings;

/**
 * @author Jáchym Toušek
 */
class ParameterFinder extends Object
{

	/** @var IPresenterFactory */
	protected $presenterFactory;

	/** @var Cache */
	protected $cache;

	/** @var string[] */
	protected $ignoredTypes = [
		'int',
		'integer',
		'float',
		'double',
		'bool',
		'boolean',
		'string',
		'array',
		'object',
		'resource',
		'null',
		'mixed',
	];

	public function __construct(IPresenterFactory $presenterFactory, IStorage $storage)
	{
		$this->presenterFactory = $presenterFactory;
		$this->cache = new Cache($storage, 'Arachne.EntityLoader');
	}

	/**
	 * Returns entity parameters based on the request.
	 * @return string[]
	 */
	public function getEntityParameters(Request $request)
	{
		return $this->cache->load($this->getCacheKey($request), function (& $dependencies) use ($request) {
			return $this->loadEntityParameters($request->getPresenterName(), $request->getParameters(), $dependencies);
		});
	}

	/**
	 * @param string $presenter
	 * @param array $parameters
	 * @param array $dependencies
	 * @return string[]
	 */
	protected function loadEntityParameters($presenter, $parameters, & $dependencies)
	{
		$class = $this->presenterFactory->getPresenterClass($presenter);
		$presenterReflection = new PresenterComponentReflection($class);
		$files = [];

		// Presenter persistent entities
		$entities = $this->getPersistentEntities($presenterReflection);
		$files[] = $presenterReflection->getFileName();

		// Action entities
		$action = $parameters[Presenter::ACTION_KEY];
		$method = 'action' . $action;
		$element = $presenterReflection->hasCallableMethod($method) ? $presenterReflection->getMethod($method) : NULL;
		if (!$element) {
			$method = 'render' . $action;
			$element = $presenterReflection->hasCallableMethod($method) ? $presenterReflection->getMethod($method) : NULL;
		}
		if ($element) {
			$entities += $this->getMethodEntities($element);
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
		if (isset($parameters[Presenter::SIGNAL_KEY])) {
			$signal = $parameters[Presenter::SIGNAL_KEY];
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

		// $dependencies is passed by reference
		$dependencies = [
			Cache::FILES => $files,
		];

		return $entities;
	}

	/**
	 * @param ClassType $reflection
	 * @param string $component
	 * @return PresenterComponentReflection|NULL
	 */
	protected function createReflection(ClassType $reflection, $component)
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
			if (!is_string($class)) {
				return;
			}
			$class = AnnotationsParser::expandClassName($class, $element->getDeclaringClass());
			if (class_exists($class)) {
				if (isset($subComponent)) {
					return $this->createReflection(new ClassType($class), $subComponent);
				} else {
					return new PresenterComponentReflection($class);
				}
			} else {
				throw new ClassNotFoundException("Class '$class' from $reflection->name::$method @return annotation not found.");
			}
		}
	}

	/**
	 * @param Method $element
	 * @param string $prefix
	 * @param string $default
	 * @return string[]
	 */
	protected function getMethodEntities(Method $reflection, $prefix = NULL)
	{
		$parameters = [];
		foreach ($reflection->getParameters() as $parameter) {
			$parameters[] = $parameter->getName();
		}
		$entities = [];
		foreach ($reflection->getParameters() as $parameter) {
			$type = $parameter->getClassName();
			if ($type) {
				$entities[$prefix . $parameter->getName()] = $type;
			}
		}
		return $entities;
	}

	/**
	 * @param PresenterComponentReflection $reflection
	 * @param string
	 * @return string[]
	 */
	protected function getPersistentEntities(PresenterComponentReflection $reflection, $prefix = NULL)
	{
		$entities = [];
		foreach ($reflection->getPersistentParams() as $persistent => $_) {
			$parameter = new Property($reflection->getName(), $persistent);
			if (!$parameter->isStatic() && $parameter->hasAnnotation('var')) {
				$type = (string) $parameter->getAnnotation('var');
				if (Strings::match($type, '/^[[:alnum:]_\\\\]++$/') && !in_array($type, $this->ignoredTypes)) {
					$entities[$prefix . $persistent] = AnnotationsParser::expandClassName($type, $parameter->getDeclaringClass());
				}
			}
		}
		return $entities;
	}

	/**
	 * @param Request $request
	 * @return string[]
	 */
	protected function getCacheKey(Request $request)
	{
		$parameters = $request->getParameters();
		$key = [
			'presenter' => $request->getPresenterName(),
			'action' => $parameters[Presenter::ACTION_KEY],
		];
		unset($parameters[Presenter::ACTION_KEY]);
		if (isset($parameters[Presenter::SIGNAL_KEY])) {
			$key['signal'] = $parameters[Presenter::SIGNAL_KEY];
			unset($parameters[Presenter::SIGNAL_KEY]);
		}
		$key['parameters'] = array_keys($parameters);
		return $key;
	}

}
