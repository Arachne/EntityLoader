<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\Application;

use Arachne\EntityLoader\Exception\TypeHintException;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\PresenterComponentReflection;
use Nette\Caching\Cache;
use Nette\ComponentModel\IComponent;
use Nette\Object;
use Nette\Reflection\AnnotationsParser;
use Nette\Reflection\ClassType;
use Nette\Reflection\Method;
use Nette\Reflection\Parameter;
use Nette\Reflection\Property;
use Nette\Utils\Strings;
use StdClass;
use Oops\CacheFactory\Caching\CacheFactory;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ParameterFinder extends Object
{

	/** @var string[] */
	private static $simpleTypes = [
		'int' => 'int',
		'integer' => 'int',
		'float' => 'float',
		'double' => 'float',
		'bool' => 'bool',
		'boolean' => 'bool',
		'string' => 'string',
		'array' => 'array',
		'object' => 'object',
		'resource' => 'resource',
		'callable' => 'callable',
		'mixed' => 'mixed',
	];

	/** @var IPresenterFactory */
	private $presenterFactory;

	/** @var Cache */
	private $cache;

	public function __construct(IPresenterFactory $presenterFactory, CacheFactory $cacheFactory)
	{
		$this->presenterFactory = $presenterFactory;
		$this->cache = $cacheFactory->create('Arachne.ParameterFinder');
	}

	/**
	 * Returns parameters information based on the request.
	 * @return StdClass[]
	 */
	public function getMapping(Request $request)
	{
		return $this->cache->load($this->getCacheKey($request), function (& $dependencies) use ($request) {
			return $this->loadMapping($request->getPresenterName(), $request->getParameters(), $dependencies);
		});
	}

	/**
	 * @param string $presenter
	 * @param array $parameters
	 * @param array $dependencies
	 * @return StdClass[]
	 */
	private function loadMapping($presenter, $parameters, & $dependencies)
	{
		$class = $this->presenterFactory->getPresenterClass($presenter);
		$presenterReflection = new PresenterComponentReflection($class);
		$files = [];

		// Presenter persistent parameters
		$info = $this->getPersistentParameters($presenterReflection);
		$files[] = $presenterReflection->getFileName();

		// Action parameters
		$action = isset($parameters[Presenter::ACTION_KEY]) ? $parameters[Presenter::ACTION_KEY] : Presenter::DEFAULT_ACTION;
		$method = 'action' . $action;
		$element = $presenterReflection->hasCallableMethod($method) ? $presenterReflection->getMethod($method) : null;
		if (!$element) {
			$method = 'render' . $action;
			$element = $presenterReflection->hasCallableMethod($method) ? $presenterReflection->getMethod($method) : null;
		}
		if ($element) {
			$info += $this->getMethodParameters($element);
			$files[] = $element->getFileName();
		}

		// Persistent component parameters
		$components = [];
		foreach ($parameters as $key => $_) {
			$pos = strrpos($key, IComponent::NAME_SEPARATOR);
			if ($pos !== false) {
				$component = substr($key, 0, $pos);
				if (!isset($components[$component])) {
					$reflection = $this->createReflection($presenterReflection, $component);
					$components[$component] = true;
					if ($reflection) {
						$files[] = $reflection->getFileName();
						$info += $this->getPersistentParameters($reflection, $component . IComponent::NAME_SEPARATOR);
					}
				}
			}
		}

		// Signal parameters
		if (isset($parameters[Presenter::SIGNAL_KEY])) {
			$signal = $parameters[Presenter::SIGNAL_KEY];
			$pos = strrpos($signal, IComponent::NAME_SEPARATOR);
			if ($pos !== false) {
				$component = substr($signal, 0, $pos);
				$signal = substr($signal, $pos + 1);
				$reflection = $this->createReflection($presenterReflection, $component);
				$prefix = $component . IComponent::NAME_SEPARATOR;
			} else {
				$reflection = $presenterReflection;
				$prefix = '';
			}
			$method = 'handle' . ucfirst($signal);
			if ($reflection && $reflection->hasCallableMethod($method)) {
				$element = $reflection->getMethod($method);
				$info += $this->getMethodParameters($element, $prefix);
				$files[] = $element->getFileName();
			}
		}

		// $dependencies is passed by reference
		$dependencies = [
			Cache::FILES => $files,
		];

		return $info;
	}

	/**
	 * @param ClassType $reflection
	 * @param string $component
	 * @return PresenterComponentReflection|null
	 */
	private function createReflection(ClassType $reflection, $component)
	{
		$pos = strpos($component, IComponent::NAME_SEPARATOR);
		if ($pos !== false) {
			$subComponent = substr($component, $pos + 1);
			$component = substr($component, 0, $pos);
		}
		if ($component === '') {
			return;
		}
		$method = 'createComponent' . ucfirst($component);
		if ($reflection->hasMethod($method)) {
			$element = $reflection->getMethod($method);
			$class = $element->getAnnotation('return');
			if (!Strings::match($class, '/^[[:alnum:]_\\\\]++$/')) {
				throw new TypeHintException("No @return annotation found for method $element.");
			}
			$class = AnnotationsParser::expandClassName($class, $element->getDeclaringClass());
			if (!class_exists($class)) {
				throw new TypeHintException("Class '$class' from $reflection->name::$method @return annotation not found.");
			}
			return isset($subComponent)
				? $this->createReflection(new ClassType($class), $subComponent)
				: new PresenterComponentReflection($class);
		}
	}

	/**
	 * @param Method $element
	 * @param string $prefix
	 * @param string $default
	 * @return StdClass[]
	 */
	private function getMethodParameters(Method $reflection, $prefix = null)
	{
		$parameters = [];
		foreach ($reflection->getParameters() as $parameter) {
			$parameters[] = $parameter->getName();
		}
		$info = [];
		foreach ($reflection->getParameters() as $parameter) {
			$type = $this->getParameterType($reflection, $parameter);
			$nullable = $parameter->isOptional();
			$info[$prefix . $parameter->getName()] = $this->createInfoObject($type, $nullable);
		}
		return $info;
	}

	/**
	 * @return string
	 */
	private function getParameterType(Method $method, Parameter $parameter)
	{
		$type = $parameter->getClassName();
		if ($type) {
			return $type;
		}
		if ($parameter->isArray()) {
			return 'array';
		}
		if ($parameter->isCallable()) {
			return 'callable';
		}
		// no typehint, check the @param annotation
		if (isset($method->getAnnotations()['param'])) {
			foreach ($method->getAnnotations()['param'] as $annotation) {
				$matches = Strings::match($annotation, '/^([[:alnum:]_\\\\]++)\\s++\\$([[:alnum:]]++)$/');
				if (!$matches) {
					throw new TypeHintException("Annotation '@param $annotation' is not valid. The correct format is '@param type \$name'. Only alphanumeric characters, '_' and '\' are allowed for the type hint.");
				}
				if ($matches[2] === $parameter->getName()) {
					return $this->normalizeType($matches[1], $method->getDeclaringClass());
				}
			}
		}
		throw new TypeHintException("No type hint found for $parameter. Specify it or use '@param mixed \${$parameter->getName()}' to allow any type.");
	}

	/**
	 * @param PresenterComponentReflection $reflection
	 * @param string
	 * @return StdClass[]
	 */
	private function getPersistentParameters(PresenterComponentReflection $reflection, $prefix = null)
	{
		$info = [];
		foreach ($reflection->getPersistentParams() as $persistent => $_) {
			$parameter = new Property($reflection->getName(), $persistent);
			if (!$parameter->isStatic() && $parameter->hasAnnotation('var')) {
				$type = (string) $parameter->getAnnotation('var');
				if (!Strings::match($type, '/^[[:alnum:]_\\\\]++$/')) {
					throw new TypeHintException("Type hint '$type' is not valid. Only alphanumeric characters, '_' and '\' are allowed.");
				}
				$info[$prefix . $persistent] = $this->createInfoObject($this->normalizeType($type, $parameter->getDeclaringClass()), true);
			}
		}
		return $info;
	}

	/**
	 * @param string $type
	 * @param ClassType $class
	 * @return string
	 */
	private function normalizeType($type, ClassType $class)
	{
		return isset(self::$simpleTypes[$type]) ? self::$simpleTypes[$type] : AnnotationsParser::expandClassName($type, $class);
	}

	/**
	 * @param string $type
	 * @param bool $nullable
	 * @return StdClass
	 */
	private function createInfoObject($type, $nullable)
	{
		$object = new StdClass();
		$object->type = $type;
		$object->nullable = $nullable;
		return $object;
	}

	/**
	 * @param Request $request
	 * @return string[]
	 */
	private function getCacheKey(Request $request)
	{
		$parameters = $request->getParameters();
		$key = [
			'presenter' => $request->getPresenterName(),
		];
		if (isset($parameters[Presenter::ACTION_KEY])) {
			$key['action'] = $parameters[Presenter::ACTION_KEY];
			unset($parameters[Presenter::ACTION_KEY]);
		}
		if (isset($parameters[Presenter::SIGNAL_KEY])) {
			$key['signal'] = $parameters[Presenter::SIGNAL_KEY];
			unset($parameters[Presenter::SIGNAL_KEY]);
		}
		$key['parameters'] = array_keys($parameters);
		return $key;
	}

}
