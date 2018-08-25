<?php

declare(strict_types=1);

namespace Arachne\EntityLoader\Application;

use Arachne\EntityLoader\Exception\TypeHintException;
use Contributte\Cache\ICacheFactory;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\UI\ComponentReflection;
use Nette\Application\UI\Presenter;
use Nette\Caching\Cache;
use Nette\ComponentModel\IComponent;
use Nette\DI\PhpReflection;
use Nette\Utils\Strings;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use stdClass;

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

    public function __construct(IPresenterFactory $presenterFactory, ICacheFactory $cacheFactory)
    {
        $this->presenterFactory = $presenterFactory;
        $this->cache = $cacheFactory->create('Arachne.ParameterFinder');
    }

    /**
     * Returns parameters information based on the request.
     *
     * @return stdClass[]
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
        if ($element === null) {
            $method = 'render'.$action;
            $element = $presenterReflection->hasCallableMethod($method) ? $presenterReflection->getMethod($method) : null;
        }
        if ($element !== null) {
            $info += $this->getMethodParameters($element);
            $files[] = $element->getFileName();
        }

        // Persistent component parameters
        $components = [];
        /** @var string $key */
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
            if ($reflection === null) {
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
            if ($reflection !== null && $reflection->hasCallableMethod($method)) {
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
    private function createReflection(ReflectionClass $reflection, string $component): ?ComponentReflection
    {
        $pos = strpos($component, IComponent::NAME_SEPARATOR);
        if ($pos !== false) {
            $subComponent = substr($component, $pos + 1);
            $component = substr($component, 0, $pos);
        }
        if ($component === '') {
            return null;
        }
        $method = 'createComponent'.ucfirst($component);
        if ($reflection->hasMethod($method)) {
            $element = $reflection->getMethod($method);
            $type = $element->getReturnType();
            if ($type === null) {
                throw new TypeHintException(sprintf('Method %s::%s has no return type.', $reflection->name, $method));
            }
            if ($type->isBuiltin()) {
                throw new TypeHintException(sprintf('Method %s::%s does not return a class.', $reflection->name, $method));
            }
            $class = (string) $type;
            if ($class === 'self') {
                $class = $element->getDeclaringClass()->getName();
            }
            if (!class_exists($class)) {
                throw new TypeHintException(sprintf('Class "%s" from %s::%s return type not found.', $class, $reflection->name, $method));
            }

            return isset($subComponent)
                ? $this->createReflection(new ComponentReflection($class), $subComponent)
                : new ComponentReflection($class);
        }

        return null;
    }

    private function getMethodParameters(ReflectionMethod $reflection, string $prefix = null): array
    {
        $info = [];
        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType() !== null ? (string) $parameter->getType() : 'mixed';
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
                if ($type !== '') {
                    if (!(bool) Strings::match($type, '/^[[:alnum:]_\\\\]++$/')) {
                        throw new TypeHintException(sprintf('Type hint "%s" is not valid. Only alphanumeric characters, "_" and "\" are allowed.', $type));
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

    private function createInfoObject(string $type, bool $optional): stdClass
    {
        $object = new stdClass();
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
