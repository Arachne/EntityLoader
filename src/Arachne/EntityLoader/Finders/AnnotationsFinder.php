<?php

namespace Arachne\EntityLoader\Finders;

use Arachne\EntityLoader\InvalidStateException;

/**
 * @author Jáchym Toušek
 */
class AnnotationsFinder extends \Nette\Object implements \Arachne\EntityLoader\IParameterFinder
{

	const ANNOTATION = 'Entity';

	/** @var \Nette\Application\IPresenterFactory */
	protected $presenterFactory;

	/** @var \Nette\Caching\Cache */
	protected $cache;

	/**
	 * @param \Nette\Application\IPresenterFactory $presenterFactory
	 * @param \Nette\Caching\IStorage $storage
	 */
	public function __construct(\Nette\Application\IPresenterFactory $presenterFactory, \Nette\Caching\IStorage $storage)
	{
		$this->presenterFactory = $presenterFactory;
		$this->cache = new \Nette\Caching\Cache($storage, 'Arachne.EntityLoader');
	}

	/**
	 * Returns entity parameters based on the request.
	 * @param \Nette\Application\Request $request
	 * @return array
	 */
	public function getEntityParameters(\Nette\Application\Request $request)
	{
		$parameters = $request->getParameters();
		$presenter = $request->getPresenterName();
		$cacheKey = $this->getCacheKey($presenter, $parameters);
		$entities = $this->cache->load($cacheKey);
		if ($entities !== NULL) {
			return $entities;
		}

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

		// Does not invalidate if a component factory file was changed (see getComponentReflection method)
		$this->cache->save($cacheKey, $entities, [
			\Nette\Caching\Cache::FILES => $files,
		]);

		return $entities;
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
			if (!is_string($class)) {
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
	protected function getMethodEntities(\Nette\Reflection\Method $reflection, $prefix = NULL)
	{
		$parameters = [];
		foreach ($reflection->getParameters() as $parameter) {
			$parameters[] = $parameter->getName();
		}
		$entities = [];
		$annotations = $reflection->getAnnotations();
		if (isset($annotations[self::ANNOTATION])) {
			foreach ($annotations[self::ANNOTATION] as $annotation) {
				if (!is_string($annotation)) {
					throw new InvalidStateException("Annotation @Entity of '$reflection->name' method is not a string.");
				}
				if (!preg_match('/^(.+)\\s++\\$(\\w++)$/', $annotation, $matches)) {
					throw new InvalidStateException("Annotation @Entity of '$reflection->name' method doesn't have correct format.");
				}
				if (!in_array($matches[2], $parameters)) {
					throw new InvalidStateException("Annotation @Entity of '$reflection->name' method uses nonexistent parameter '$$matches[2]'.");
				}
				$entities[$prefix . $matches[2]] = $matches[1];
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
			if (isset($annotations[self::ANNOTATION])) {
				foreach ($annotations[self::ANNOTATION] as $annotation) {
					if (!is_string($annotation)) {
						throw new InvalidStateException("Annotation @Entity of '$$persistent' property is not a string.");
					}
					$entities[$prefix . $persistent] = $annotation;
				}
			}
		}
		return $entities;
	}

	/**
	 * @param string $presenter
	 * @param array $parameters
	 * @return array
	 */
	protected function getCacheKey($presenter, array $parameters)
	{
		$key = [
			'presenter' => $presenter,
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

}
