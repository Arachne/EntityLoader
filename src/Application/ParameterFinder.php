<?php

namespace Arachne\EntityLoader\Application;

use Arachne\EntityLoader\Exception\TypeHintException;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\UI\ComponentReflection;
use Nette\Application\UI\Presenter;
use Nette\Caching\Cache;
use Nette\ComponentModel\IComponent;
use Nette\DI\PhpReflection;
use Nette\Utils\Strings;
use Oops\CacheFactory\Caching\CacheFactory;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use StdClass;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ParameterFinder
{
    /**
     * @var string[]
     */
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

    /**
     * @var IPresenterFactory
     */
    private $presenterFactory;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(IPresenterFactory $presenterFactory, CacheFactory $cacheFactory)
    {
        $this->presenterFactory = $presenterFactory;
        $this->cache = $cacheFactory->create('Arachne.ParameterFinder');
    }

    /**
     * Returns parameters information based on the request.
     *
     * @return StdClass[]
     */
    public function getMapping(Request $request): array
    {
        return $this->cache->load($this->getCacheKey($request), function (&$dependencies) use ($request) {
            return $this->loadMapping($request->getPresenterName(), $request->getParameters(), $dependencies);
        });
    }

    private function loadMapping(string $presenter, array $parameters, array &$dependencies = null): array
    {
        $class = $this->presenterFactory->getPresenterClass($presenter);
        $presenterReflection = new ComponentReflection($class);
        $files = [];

        // Presenter persistent parameters
        $info = $this->getPersistentParameters($presenterReflection);
        $files[] = $presenterReflection->getFileName();

        // Action parameters
        $action = isset($parameters[Presenter::ACTION_KEY]) ? $parameters[Presenter::ACTION_KEY] : Presenter::DEFAULT_ACTION;
        $method = 'action'.$action;
        $element = $presenterReflection->hasCallableMethod($method) ? $presenterReflection->getMethod($method) : null;
        if (!$element) {
            $method = 'render'.$action;
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
            if ($pos === false) {
                continue;
            }
            $component = substr($key, 0, $pos);
            if (isset($components[$component])) {
                continue;
            }
            $reflection = $this->createReflection($presenterReflection, $component);
            $components[$component] = true;
            if (!$reflection) {
                continue;
            }
            $files[] = $reflection->getFileName();
            $info += $this->getPersistentParameters($reflection, $component.IComponent::NAME_SEPARATOR);
        }

        // Signal parameters
        if (isset($parameters[Presenter::SIGNAL_KEY])) {
            $signal = $parameters[Presenter::SIGNAL_KEY];
            $pos = strrpos($signal, IComponent::NAME_SEPARATOR);
            if ($pos !== false) {
                $component = substr($signal, 0, $pos);
                $signal = substr($signal, $pos + 1);
                $reflection = $this->createReflection($presenterReflection, $component);
                $prefix = $component.IComponent::NAME_SEPARATOR;
            } else {
                $reflection = $presenterReflection;
                $prefix = '';
            }
            $method = 'handle'.ucfirst($signal);
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
     * @return ComponentReflection|null
     */
    private function createReflection(ReflectionClass $reflection, string $component)
    {
        $pos = strpos($component, IComponent::NAME_SEPARATOR);
        if ($pos !== false) {
            $subComponent = substr($component, $pos + 1);
            $component = substr($component, 0, $pos);
        }
        if ($component === '') {
            return;
        }
        $method = 'createComponent'.ucfirst($component);
        if ($reflection->hasMethod($method)) {
            $element = $reflection->getMethod($method);
            $type = $element->getReturnType();
            if (!$type) {
                throw new TypeHintException("Method $reflection->name::$method has no return type.");
            }
            if ($type->isBuiltin()) {
                throw new TypeHintException("Method $reflection->name::$method does not return a class.");
            }
            $class = (string) $type;
            if (!class_exists($class)) {
                throw new TypeHintException("Class '$class' from $reflection->name::$method return type not found.");
            }

            return isset($subComponent)
                ? $this->createReflection(new ComponentReflection($class), $subComponent)
                : new ComponentReflection($class);
        }
    }

    private function getMethodParameters(ReflectionMethod $reflection, string $prefix = null): array
    {
        $info = [];
        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType() ? (string) $parameter->getType() : 'mixed';
            $optional = $parameter->isOptional();
            $info[$prefix.$parameter->getName()] = $this->createInfoObject($type, $optional);
        }

        return $info;
    }

    private function getPersistentParameters(ComponentReflection $reflection, string $prefix = null): array
    {
        $info = [];
        foreach ($reflection->getPersistentParams() as $persistent => $_) {
            $parameter = new ReflectionProperty($reflection->getName(), $persistent);
            if (!$parameter->isStatic()) {
                $type = (string) PhpReflection::parseAnnotation($parameter, 'var');
                if ($type) {
                    if (!Strings::match($type, '/^[[:alnum:]_\\\\]++$/')) {
                        throw new TypeHintException("Type hint '$type' is not valid. Only alphanumeric characters, '_' and '\' are allowed.");
                    }
                    $info[$prefix.$persistent] = $this->createInfoObject($this->normalizeType($type, $parameter->getDeclaringClass()), true);
                }
            }
        }

        return $info;
    }

    private function normalizeType(string $type, ReflectionClass $class): string
    {
        return isset(self::$simpleTypes[$type]) ? self::$simpleTypes[$type] : PhpReflection::expandClassName($type, $class);
    }

    private function createInfoObject(string $type, bool $optional): StdClass
    {
        $object = new StdClass();
        $object->type = $type;
        $object->optional = $optional;

        return $object;
    }

    private function getCacheKey(Request $request): array
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
