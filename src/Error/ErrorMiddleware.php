<?php

namespace EnderLab\Error;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorMiddleware implements MiddlewareInterface
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * ErrorMiddleware constructor.
     *
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @throws \Exception
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            if (!(error_reporting() & $errno)) {
                return;
            }
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        try {
            $response = $delegate->process($request);

            if (!$response instanceof ResponseInterface) {
                throw new \Exception('Application did not return a response', 500);
            }
        } catch (\Exception | \Throwable $e) {
            $response = $this->response->withStatus($e->getCode());
            $response->getBody()->write($e->getMessage());
        }

        restore_error_handler();

        return $response;
    }
}
