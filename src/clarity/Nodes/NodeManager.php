<?php

namespace framework\clarity\Nodes;

use Exception;
use framework\clarity\Container\DIContainer;
use framework\clarity\Container\interfaces\ContainerInterface;
use framework\clarity\Container\interfaces\ParameterStorageInterface;
use framework\clarity\EventDispatcher\EventDispatcher;
use framework\clarity\EventDispatcher\interfaces\EventDispatcherInterface;
use framework\clarity\Helpers\Alias;
use framework\clarity\Helpers\PathHelper;
use framework\clarity\Http\Response;
use framework\clarity\Http\router\exceptions\HttpNotFoundException;
use framework\clarity\Http\ServerRequest;
use framework\clarity\Kernel\ConfigService;
use framework\clarity\Kernel\KernelEvents;
use framework\clarity\Kernel\messages\ExceptionMessage;
use framework\clarity\Nodes\interfaces\NodeManagerInterface;
use framework\clarity\Nodes\listeners\NodeManagerKernelExceptionListener;
use InvalidArgumentException;
use Throwable;

class NodeManager implements NodeManagerInterface
{
    private string $path;

    private array $nodes = [];

    /**
     * @param ContainerInterface $container
     * @param ParameterStorageInterface $paramStorage
     * @throws Exception
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ParameterStorageInterface $paramStorage,
    ) {
        $this->init();
    }

    /**
     * @throws Exception
     */
    public function init(): void
    {
        $config = $this->paramStorage->get('nodes');

        if (empty($config['base_dir']) === true) {
            throw new InvalidArgumentException('Не найдена базовая директория для регистрации узлов системы');
        }

        $this->path = str_starts_with($config['base_dir'], '@') === true
            ? Alias::get($config['base_dir'])
            : PathHelper::joinPaths($this->paramStorage->get('kernel.project_dir'), $config['nodes.base_dir']);


        $nodes = array_filter($config['register'] ?? [], 'is_array');

        $this->registerNodes($nodes);
    }

    /**
     * @param array $nodes
     * @return void
     */
    private function registerNodes(array $nodes): void
    {
        foreach ($nodes as $name => $node) {

            if (isset($node['class']) === false) {
                continue;
            }

            $class = $node['class'];

            if (
                class_exists($class) === true
                && is_subclass_of($class, Node::class, true) === true
            ) {
                try {

                    $nodeInstance = $this->container->build($class, [
                        'name' => $name,
                        'path' => $this->path . DIRECTORY_SEPARATOR . $name,
                    ]);

                    $nodeInstance->setStatus($node['enabled'] ?? false);

                    $this->nodes[$name] = [
                        'node' => $nodeInstance,
                        'status' => $nodeInstance->getStatus(),
                    ];

                    $nodeInstance->init();

                } catch (Throwable $e) {

                    // TODO: Do Something
                }
            }
        }
    }

    /**
     * @param string $name
     * @return array|null
     */
    public function getNode(string $name): ?array
    {
        return $this->nodes[$name] ?? null;
    }

    /**
     * @return array
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }
}