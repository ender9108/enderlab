<?php

namespace EnderLab\MiddleEarth\Dispatcher;

use Psr\Http\Server\RequestHandlerInterface;

interface DispatcherInterface extends RequestHandlerInterface
{
    /**
     * @param $middleware
     *
     * @return Dispatcher
     */
    public function pipe($middleware): Dispatcher;
}
