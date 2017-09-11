<?php

namespace EnderLab\Router;

use GuzzleHttp\Psr7\Response;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TrailingSlashMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $path = (string) $request->getUri()->getPath();

        if ($path != '/' && substr($path, -1) == '/') {
            $uri = $request->getUri()->withPath(substr($path, 0, -1));

            if ($request->getMethod() == 'GET') {
                return (new Response())
                    ->withHeader('Location', (string) $uri)
                    ->withStatus(301);
            } else {
                return $delegate->process($request->withUri($uri));
            }
        }

        return $delegate->process($request);
    }
}
