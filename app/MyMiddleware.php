<?php
namespace App;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class MyMiddleware implements MiddlewareInterface
{
    private $container;

    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

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