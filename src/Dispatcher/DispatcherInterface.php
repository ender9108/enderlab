<?php

namespace EnderLab\Dispatcher;

use Interop\Http\Server\RequestHandlerInterface;

interface DispatcherInterface extends RequestHandlerInterface
{
    /**
     * @param $middleware
     *
     * @return Dispatcher
     */
    public function pipe($middleware): Dispatcher;
}
