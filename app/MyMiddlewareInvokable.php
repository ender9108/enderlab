<?php
namespace App;

use Interop\Http\ServerMiddleware\DelegateInterface;
use \Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class MyMiddlewareInvokable
{
  public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
  {
      $response = $delegate->process($request);
      $response->getBody()->write('<br>Invokable (by route) !!!<br>');

      return $response;
  }
}