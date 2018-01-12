<?php

namespace EnderLab\MiddleEarth\Error;

use EnderLab\MiddleEarth\Middleware\BaseMiddleware;
use GuzzleHttp\Psr7\Response;

class NotFoundMiddleware extends BaseMiddleware
{
    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $requestHandler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler): ResponseInterface
    {
        $response = new Response();

        return $response;
    }
}
