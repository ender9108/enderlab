<?php

namespace EnderLab\Logger;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class LoggerMiddleware implements MiddlewareInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * LoggerMiddleware constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $this->logger->info(
            'Request: ' . "\n" .
            "\t" . 'Method: ' . $request->getMethod() . "\n" .
            "\t" . 'Uri: ' . $request->getUri() . "\n" .
            "\t" . 'Headers: ' . print_r($request->getHeaders(), true) . "\n" .
            "\t" . 'Server: ' . print_r($request->getServerParams(), true) . "\n" .
            "\t" . 'Query: ' . print_r($request->getQueryParams(), true) . "\n" .
            "\t" . 'Body: ' . print_r($request->getParsedBody(), true) . "\n" .
            "\t" . 'Upload: ' . print_r($request->getUploadedFiles(), true) . "\n"
        );

        return $delegate->process($request);
    }
}
