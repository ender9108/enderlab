<?php
namespace App;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use \Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class MyMiddleware implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $response = $delegate->process($request);
        $response->getBody()->write('<br>Coucou 4 (by route)<br>');

        return $response;
    }
}