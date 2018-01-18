<?php

namespace EnderLab\MiddleEarth\Renderer;

use League\Plates\Engine;
use Psr\Container\ContainerInterface;

class PlatesRedererFactory
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
            //$engine->loadExtension(new ChangeCase());
        }

        return $engine;
    }
}
