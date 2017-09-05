<?php

namespace EnderLab\Dispatcher;

use Interop\Http\ServerMiddleware\DelegateInterface;

interface DispatcherInterface extends DelegateInterface
{
    /**
     * @param $middleware
     *
     * @return Dispatcher
     */
    public function pipe($middleware): Dispatcher;
}
