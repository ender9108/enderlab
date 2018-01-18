<?php

namespace EnderLab\MiddleEarth\Loader;

use EnderLab\MiddleEarth\Dispatcher\Dispatcher;
use EnderLab\MiddleEarth\Router\Router;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Container\ContainerInterface;
use ReflectionClass;

class LazyLoading
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function load(string $classname)
    {
        if (!class_exists($classname)) {
            throw new InvalidArgumentException(
                sprintf('Unable to create object "%s"; not a valid classname', $classname)
            );
        }

        $reflection = new ReflectionClass($classname);
        $args = $this->getParameters($reflection);

        $instance = $reflection->newInstanceArgs($args);

        return $instance;
    }

    /**
     * @param ReflectionClass $reflection
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return array
     */
    private function getParameters(ReflectionClass $reflection): array
    {
        if (null === $reflection->getConstructor()) {
            return [];
        }

        $params = $reflection->getConstructor()->getParameters();
        $args = [];

        foreach ($params as $param) {
            if ($param->getClass() &&
                $param->getClass()->isInstance($this->container)
            ) {
                $args[] = $this->container;
            } elseif ($param->getClass() &&
                $this->container->get('logger.engine') &&
                $param->getClass()->implementsInterface('Psr\\Log\\LoggerInterface')
            ) {
                $args[] = $this->container->get('logger.engine');
            } elseif ($this->container->has(Router::class) &&
                $param->getClass() &&
                $param->getClass()->isInstance(Router::class)
            ) {
                $args[] = $this->container->get(Router::class);
            } elseif ($param->getClass() &&
                $param->getClass()->isInstance(Dispatcher::class)
            ) {
                $args[] = $this->container->get(Dispatcher::class);
            } elseif ($this->container->has(Response::class) &&
                $param->getClass() &&
                $param->getClass()->isInstance(Response::class)
            ) {
                $args[] = $this->container->get(Response::class);
            } else {
                $request = ServerRequest::fromGlobals();
                $attributes = $request->getQueryParams();

                foreach ($attributes as $name => $value) {
                    if (array_key_exists($param->getName(), $name)) {
                        if ($param->isVariadic() && is_array($value)) {
                            $args[] = array_merge($args, array_values($value));
                        } else {
                            $args[] = $value;
                        }
                    } elseif ($param->isDefaultValueAvailable()) {
                        $args[] = $param->getDefaultValue();
                    } else {
                        if ($param->hasType() && $param->allowsNull()) {
                            $args[] = null;
                        }
                    }
                } // endforeach $attributes
            } // endif $param type
        } // endforeach $params

        return $args;
    }
}
