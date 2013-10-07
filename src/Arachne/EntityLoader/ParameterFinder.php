<?php

/**
 * This file is part of the Arachne EntityLoader extenstion
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\PresenterComponentReflection;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Object;
use Nette\Reflection\ClassType;
use Nette\Reflection\Method;
use Nette\Reflection\Property;

/**
 * @author Jáchym Toušek
 */
class ParameterFinder extends Object
{

	/** @var IPresenterFactory */
	protected $presenterFactory;

	/** @var Cache */
	protected $cache;

	public function __construct(IPresenterFactory $presenterFactory, IStorage $storage)
	{
		$this->presenterFactory = $presenterFactory;
		$this->cache = new Cache($storage, 'Arachne.EntityLoader');
	}

	/**
	 * Returns entity parameters based on the request.
	 * @return array
	 */
	public function getEntityParameters(Request $request)
	{
		$parameters = $request->getParameters();
		$presenter = $request->getPresenterName();
		$cacheKey = $this->getCacheKey($request);
		$cached = $this->cache->load($cacheKey);
		if ($cached !== NULL) {
			return $cached;
		}

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

		// Does not invalidate if a component factory file was changed (see createReflection method)
		$this->cache->save($cacheKey, $entities, [
			Cache::FILES => $files,
		]);

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
			if ($class[0] != '\\') {
				$class = $reflection->getNamespaceName() . '\\' . $class;
			}
			if (class_exists($class)) {
				if (isset($subComponent)) {
					return $this->createReflection(new ClassType($class), $subComponent);
				} else {
					return new PresenterComponentReflection($class);
				}
			} else {
				throw new InvalidStateException("Class '$class' from $reflection->name::$method @return annotation not found.");
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
				// TODO: Use parser from Doctrine/Annotarions to get correct class from use statements
				$entities[$prefix . $persistent] = $parameter->getAnnotation('var');
			}
		}
		return $entities;
	}

	/**
	 * @param Request $request
	 * @return array
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
