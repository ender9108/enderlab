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
        set_error_handler(
            function (
                int $errno,
                string $errstr,
                ?string $errfile = null,
                ?int $errline = null,
                ?array $errcontext = null
            ) {
                if (!(error_reporting() & $errno)) {
                    return;
                }

                throw new \ErrorException($errstr, 500, 1, $errfile, $errline);
            }
        );

        try {
            $response = $delegate->process($request);

            if (!$response instanceof ResponseInterface) {
                throw new \Exception('Application did not return a response', 500);
            }
        } catch (\Exception | \Throwable $e) {
            $response = $this->response->withStatus($e->getCode());
            $response->getBody()->write('Error: '.$e->getMessage());
        }

        restore_error_handler();

        return $response;
    }
}
