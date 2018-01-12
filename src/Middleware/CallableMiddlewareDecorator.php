<?php

namespace EnderLab\MiddleEarth\Middleware;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CallableMiddlewareDecorator implements MiddlewareInterface
{
    private $middleware;

    public function __construct($middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $requestHandler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler): ResponseInterface
    {
        $response = call_user_func_array($this->middleware, [$request, $requestHandler]);

        if (!$response instanceof ResponseInterface) {
            throw new \RuntimeException('No valid response sending.');
        }

        return $response;
    }
}
