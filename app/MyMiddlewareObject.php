<?php
namespace App;

use Interop\Http\ServerMiddleware\DelegateInterface;
use \Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class MyMiddlewareObject
{
  public function test(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
  {
      $response = $delegate->process($request);
      $response->getBody()->write('<br>Object !!!<br>');

      return $response;
  }
}