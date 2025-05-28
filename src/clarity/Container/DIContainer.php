<?php
declare(strict_types=1);

namespace framework\clarity\Container;

use framework\clarity\Container\exceptions\ServiceNotFoundException;
use framework\clarity\Container\exceptions\ServiceResolutionException;
use framework\clarity\Container\interfaces\ContainerInterface;
use framework\clarity\Container\interfaces\ParameterStorageInterface;
use framework\clarity\Logger\observers\TagUpdatePreventListener;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionType;
use ReflectionUnionType;

class DIContainer implements ContainerInterface
{
    private static ?DIContainer $instance = null;
    private array $services = [];

    protected function __construct(private array $config = []) {}

    /**
     * @param array $config
     * @return self
     */
    public static function create(array $config = []): self
    {
        if (isset(self::$instance) === true) {
            throw new LogicException('DIContainer is already set.');
        }

        self::$instance = new static($config);

        $container = self::$instance;

        foreach ($config['container']['services'] as $id => $serviceConfig) {
            $container->register($id, $serviceConfig);
        }

        return self::$instance;
    }

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return self::$instance ?? self::create();
    }

    /**
     * @inheritDoc
     */
    public function build(string $dependencyName, array $args = []): object
    {
        if ($dependencyName === ContainerInterface::class || $dependencyName === self::class) {
            return $this;
        }

        if (isset($this->services[$dependencyName]) === false) {

            if (
                isset($this->config['container']['autoconfigure']) === true
                && $this->config['container']['autoconfigure'] === true
            ) {
                $this->register($dependencyName, [
                        //'class' => $dependencyName,
                        'autowire' => true,
                        'arguments' => $args,
                ]);

                return $this->build($dependencyName, $args);
            }

            throw new ServiceNotFoundException($dependencyName);
        }

        $serviceConfig = $this->services[$dependencyName];

        if (empty($serviceConfig['class']) === true || class_exists($serviceConfig['class']) === false) {
            throw new LogicException("Класс {$serviceConfig['class']} не существует или не указан.");
        }

        $arguments = $serviceConfig['arguments'] ?? $args;

        try {
            $reflection = new ReflectionClass($serviceConfig['class']);

            $this->checkIfInstantiable($reflection, $dependencyName);

            $constructor = $reflection->getConstructor();

            if ($constructor === null) {
                $instance = $reflection->newInstance();

                $this->services[$dependencyName]['instance'] = $instance;

                return $instance;
            }

            $parameters = $constructor->getParameters();

            $resolvedArgs = $this->resolveConstructorArguments($parameters, $arguments, $serviceConfig, $dependencyName);

            $instance =  $reflection->newInstanceArgs($resolvedArgs);

            $this->services[$dependencyName]['instance'] = $instance;

            return $instance;

        } catch (ReflectionException $e) {
            throw new LogicException("Ошибка при анализе класса {$dependencyName}: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function call(object|string $handler, string $method, array $args = []): mixed
    {
        $instance = is_string($handler) ? $this->get($handler) : $handler;

        try {
            $refMethod = new \ReflectionMethod($instance, $method);
        } catch (\ReflectionException $e) {
            throw new \LogicException(
                "Cannot reflect {$method} on " . get_class($instance) . ": " . $e->getMessage(),
                0,
                $e
            );
        }

        $resolved = [];

        foreach ($refMethod->getParameters() as $param) {
            $name = $param->getName();
            $type = $param->getType();

            // 1) Если передали явно — используем
            if (array_key_exists($name, $args)) {
                $resolved[] = $this->resolveArgument($args[$name], $type);
                continue;
            }

            // 2) UNION-типы (например, string|int) — подставляем «заглушку»
            if ($type instanceof \ReflectionUnionType) {
                // выберем первый из union, который является builtin
                $stub = null;
                foreach ($type->getTypes() as $sub) {
                    if ($sub->isBuiltin()) {
                        switch ($sub->getName()) {
                            case 'string': $stub = ''; break 2;
                            case 'int':    $stub = 0;  break 2;
                            case 'float':  $stub = 0.0;break 2;
                            case 'bool':   $stub = false;break 2;
                        }
                    }
                }
                // если ничего не нашли — null
                $resolved[] = $stub;
                continue;
            }

            // 3) Именованный класс — автоподставляем
            if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
                $resolved[] = $this->getOrAutowire($type->getName());
                continue;
            }

            // 4) Значение по-умолчанию — используем
            if ($param->isDefaultValueAvailable()) {
                $resolved[] = $param->getDefaultValue();
                continue;
            }

            // 5) Больше некуда — ошибка
            throw new ServiceResolutionException(get_class($instance), $name);
        }

        return $refMethod->invokeArgs($instance, $resolved);
    }


    /**
     * @inheritDoc
     */
    public function get(string $id): object
    {
        if ($id === ContainerInterface::class || $id === self::class) {
            return $this;
        }

        if (isset($this->services[$id]['instance']) === true) {
            return $this->services[$id]['instance'];
        }

        if (isset($this->services[$id]['factory']) === true) {

            $factoryMethod = $this->services[$id]['factory'];

            $reflection = new ReflectionFunction($factoryMethod);

            $parameters = $reflection->getParameters();

            $resolvedParameters = $this->resolveConstructorArguments(
                $parameters,
                $this->services[$id]['arguments'] ?? [],
                $this->services[$id],
                $id
            );

            $instance = $factoryMethod(...$resolvedParameters);

            if ($this->services[$id]['singleton'] === true) {
                $this->services[$id]['instance'] = $instance;
            }

            //$this->services[$id]['instance'] = $instance;

            return $instance;
        }

        return $this->build($id);
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]) === true;
    }

    /**
     * @param string $id
     * @param array $serviceConfig
     * @return void
     */
    public function register(string $id, array $serviceConfig): void
    {
        if (isset($this->services[$id])) {
            // Логика для перезаписи или выбрасывания ошибки
            throw new LogicException("Сервис с идентификатором {$id} уже существует.");
        }

        if (isset($this->services[$id]) === true) {
            $this->services[$id] = [
                'class' => $serviceConfig['class'] ?? $this->services[$id]['class'],
                'singleton' => $serviceConfig['singleton'] ?? $this->services[$id]['singleton'] ?? true,
                'arguments' => $serviceConfig['arguments'] ?? $this->services[$id]['arguments'] ?? [],
                'tags' => $serviceConfig['tags'] ?? $this->services[$id]['tags'] ?? [],
                'autowire' => $serviceConfig['autowire'] ?? $this->services[$id]['autowire'] ?? null,
            ];

            return;
        }

        if (isset($serviceConfig['class']) === true) {
            $this->services[$id] = [
                'class' => $serviceConfig['class'],
                'singleton' => $serviceConfig['singleton'] ?? $this->services[$id]['singleton'] ?? true,
                'arguments' => $serviceConfig['arguments'] ?? [],
                'tags' => $serviceConfig['tags'] ?? [],
                'autowire' => $serviceConfig['autowire'] ?? null,
            ];

            return;
        }

        if (isset($serviceConfig['factory']) === true) {
            $this->services[$id] = [
                'factory' => $serviceConfig['factory'],
                'singleton' => $serviceConfig['singleton'] ?? $this->services[$id]['singleton'] ?? true,
                'arguments' => $serviceConfig['arguments'] ?? [],
                'tags' => $serviceConfig['tags'] ?? [],
                'autowire' => $serviceConfig['autowire'] ?? null,
            ];

            return;
        }

        if (class_exists($id) === true) {
            $this->services[$id] = [
                'class' => $id,
                'singleton' => $serviceConfig['singleton'] ?? $this->services[$id]['singleton'] ?? true,
                'arguments' => $serviceConfig['arguments'] ?? [],
                'tags' => $serviceConfig['tags'] ?? [],
                'autowire' => $serviceConfig['autowire'] ?? null,
            ];

            return;
        }

        if (interface_exists($id) === true) {
            $className = $this->getClassForInterface($id);

            if ($className === null) {
                throw new LogicException("Не удалось найти реализацию для интерфейса {$id}.");
            }

            $this->services[$id] = [
                'class' => $className,
                'singleton' => $serviceConfig['singleton'] ?? true,
                'arguments' => $serviceConfig['arguments'] ?? [],
                'tags' => $serviceConfig['tags'] ?? [],
                'autowire' => $serviceConfig['autowire'] ?? null,
            ];
        }
    }

    /**
     * Разрешение имени класса по конвенции
     *
     * @param string $interface
     * @return string|null
     */
    private function getClassForInterface(string $interface): ?string
    {
        $interfaceDirectories = $config['container']['convention']['dirs'] ?? ['Interfaces'];

        $suffixes = $config['container']['convention']['suffix'] ?? ['Interface'];

        $parts = explode('\\', $interface);

        $baseName = array_pop($parts);

        foreach ($suffixes as $suffix) {
            if (str_ends_with($baseName, $suffix) === true) {
                $baseName = substr($baseName, 0, -strlen($suffix));

                break;
            }
        }

        foreach ($interfaceDirectories as $directory) {
            if (strtolower(end($parts) )=== strtolower($directory)) {
                array_pop($parts);

                break;
            }
        }

        return implode('\\', $parts) . '\\' . $baseName;
    }

    /**
     * @param string $name
     * @return object
     */
    private function getOrAutowire(string $name): object
    {
        if ($this->has($name) === false) {
            $this->register($name, [
                'autowire' => true,
            ]);
        }

        return $this->get($name);
    }

    /**
     * @param ReflectionClass $reflection
     * @param string $dependencyName
     * @return void
     */
    private function checkIfInstantiable(ReflectionClass $reflection, string $dependencyName): void
    {
        if ($reflection->isInstantiable() === false) {
            throw new LogicException("Класс {$dependencyName} нельзя создать (возможно, это интерфейс или абстрактный класс).");
        }
    }

    /**
     * @param array $parameters
     * @param array $arguments
     * @param array $serviceConfig
     * @param string $dependency
     * @return array
     */
    private function resolveConstructorArguments(array $parameters, array $arguments, array $serviceConfig, string $dependency): array
    {
        $resolvedArgs = [];

        foreach ($parameters as $index => $parameter) {
            $paramName = $parameter->getName();

            $paramType = $parameter->getType();

            // Аргумент передан по индексу
            if (isset($arguments[$index]) === true) {
                $resolvedArgs[] = $this->resolveArgument($arguments[$index], $paramType);
                continue;
            }

            // Аргумент передан по имени
            if (isset($arguments[$paramName]) === true) {
                $resolvedArgs[] = $this->resolveArgument($arguments[$paramName], $paramType);
                continue;
            }

            // Если включен autowire
            if ($paramType !== null && $paramType->isBuiltin() === false) {
                if ($this->shouldAutowire($serviceConfig) === true) {
                    $resolvedArgs[] = $this->getOrAutowire($paramType->getName());

                    continue;
                }

                throw new ServiceResolutionException($dependency, $parameter->getName());
            }

            // Если есть дефолтное значение
            if ($parameter->isDefaultValueAvailable() === true) {
                $resolvedArgs[] = $parameter->getDefaultValue();

                continue;
            }

            throw new ServiceResolutionException($dependency, $parameter->getName());
        }

        return $resolvedArgs;
    }

    /**
     * @param mixed $argValue
     * @param ReflectionType|null $paramType
     * @return mixed
     */
    private function resolveArgument(mixed $argValue, ?ReflectionType $paramType): mixed
    {
        if ($paramType instanceof ReflectionUnionType === true) {
            return $argValue;
        }

        if (is_string($argValue) === true && $paramType !== null && $paramType->isBuiltin() === true) {
            return $argValue;
        }

        if (is_array($argValue) === true || is_object($argValue) === true) {
            return $argValue;
        }

        if (is_string($argValue) === true) {
            return $this->get($argValue);
        }

        return $argValue;
    }

    /**
     * @param array $serviceConfig
     * @return bool
     */
    private function shouldAutowire(array $serviceConfig): bool
    {
        if (isset($serviceConfig['autowire']) === true){
            return (bool) $serviceConfig['autowire'];
        }

        if (isset($this->config['container']['autowire']) === true) {
            return (bool) $this->config['container']['autowire'];
        }

        return false;
    }
}