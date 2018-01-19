<?php

namespace EnderLab\MiddleEarth\Renderer;

use EnderLab\MiddleEarth\Loader\LazyLoading;
use League\Plates\Engine;
use Psr\Container\ContainerInterface;

class PlatesRendererFactory
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create()
    {
        if (false === $this->container->has('renderer.template.path')) {
            throw new \Exception('You must define a template path in the config file.');
        }

        $ext = $this->container->has('renderer.template.ext') ? $this->container->get('renderer.template.ext') : 'phtml';
        $path = rtrim($this->container->get('renderer.template.path'), '/') . '/';

        $engine = new Engine($path, $ext);

        if ($this->container->has('renderer.template.namespaces')) {
            $namespaces = (
                is_array($this->container->get('renderer.template.namespaces')) ?
                $this->container->get('renderer.template.namespaces') :
                [$this->container->get('renderer.template.namespaces')]
            );

            foreach ($namespaces as $namespace) {
                if (is_dir($path . $namespace)) {
                    $engine->addFolder($namespace, $path . $namespace);
                }
            }
        }

        if ($this->container->has('renderer.template.plugin')) {
            $loader = new LazyLoading($this->container);
            $plugins = $this->container->get('renderer.template.plugin');

            if (false === is_array($plugins)) {
                $plugins = [$plugins];
            }

            foreach ($plugins as $plugin) {
                $instance = $loader->load($plugin);
                $engine->loadExtension($instance);
            }
        }

        return $engine;
    }
}
