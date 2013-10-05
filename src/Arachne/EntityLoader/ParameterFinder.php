<?php

/**
 * This file is part of the Arachne EntityLoader extenstion
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

use Doctrine\Common\Annotations\Reader;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\PresenterComponentReflection;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Object;
use Nette\Reflection\ClassType;
use Nette\Reflection\Method;

/**
 * @author Jáchym Toušek
 */
class ParameterFinder extends Object
{

	/** @var Reader */
	protected $reader;

	/** @var IPresenterFactory */
	protected $presenterFactory;

	/** @var Cache */
	protected $cache;

	/**
	 * @param Reader $reader
	 * @param IPresenterFactory $presenterFactory
	 * @param IStorage $storage
	 */
	public function __construct(Reader $reader, IPresenterFactory $presenterFactory, IStorage $storage)
	{
		$this->reader = $reader;
		$this->presenterFactory = $presenterFactory;
		$this->cache = new Cache($storage, 'Arachne.EntityLoader');
	}

	/**
	 * Returns entity parameters based on the request.
	 * @param Request $request
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
	 * @return array
	 */
	protected function getMethodEntities(Method $reflection, $prefix = NULL)
	{
		$parameters = [];
		foreach ($reflection->getParameters() as $parameter) {
			$parameters[] = $parameter->getName();
		}
		$entities = [];
		$annotations = $this->reader->getMethodAnnotations($reflection);
		foreach ($annotations as $annotation) {
			if (!$annotation instanceof Entity) {
				continue;
			}
			if (!in_array($annotation->parameter, $parameters)) {
				throw new InvalidStateException("Annotation @Entity of '$reflection->name' method uses nonexistent parameter '\${$annotation->parameter}'.");
			}
			$entities[$prefix . $annotation->parameter] = $annotation;
		}
		return $entities;
	}

	/**
	 * @param PresenterComponentReflection $reflection
	 * @param string
	 * @return array
	 */
	protected function getPersistentEntities(PresenterComponentReflection $reflection, $prefix = NULL)
	{
		$entities = [];
		foreach ($reflection->getPersistentParams() as $persistent => $_) {
			$annotations = $this->reader->getPropertyAnnotations($reflection->getProperty($persistent));
			foreach ($annotations as $annotation) {
				if (!$annotation instanceof Entity) {
					continue;
				}
				// TODO $annotation->parameter = $persistent; ?
				$entities[$prefix . $persistent] = $annotation;
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
