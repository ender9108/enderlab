<?php

namespace EnderLab\Router;

use GuzzleHttp\Psr7\Response;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouterMiddleware implements MiddlewareInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var Response
     */
    private $response;

    /**
     * RouterMiddleware constructor.
     *
     * @param RouterInterface $router
     * @param Response        $response
     */
    public function __construct(RouterInterface $router, Response $response)
    {
        $this->router = $router;
        $this->response = $response;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @throws RouterException
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $request = $request->withUri(
            $request->getUri()->withPath(rtrim($request->getUri()->getPath(), '/'))
        );
        $route = $this->router->match($request);

        if (null === $route) {
            throw new RouterException('Route not found for path "' . $request->getUri()->getPath() . '"', 404);
        }

        $request = $request->withAttribute(Route::class, $route);

        foreach ($route->getParams() as $label => $value) {
            $request = $request->withAttribute($label, $value);
        }

        $response = $delegate->process($request);

        return $response;
    }
}
