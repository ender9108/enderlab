<?php
namespace EnderLab\MiddleEarth\Http;

use GuzzleHttp\Psr7\Response;

class ResponseFactory
{
    public static function toJson(
        int $status = 200,
        $body = null,
        array $headers = [],
        string $version = '1.1',
        string $reason = null
    )
    {
        $body = json_encode($body, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to encode data to JSON in %s: %s',
                __CLASS__,
                json_last_error_msg()
            ));
        }

        return new Response(
            $status,
            $body,
            array_merge($headers, array('Content-Type' => 'application/json')),
            $version,
            $reason
        );
    }

    public static function toText(
        int $status = 200,
        $body = null,
        array $headers = [],
        string $version = '1.1',
        string $reason = null
    )
    {
        return new Response(
            $status,
            $body,
            array_merge($headers, array('Content-Type' => 'text/plain; charset=utf-8')),
            $version,
            $reason
        );
    }

    public static function toHtml(
        int $status = 200,
        $body = null,
        array $headers = [],
        string $version = '1.1',
        string $reason = null
    )
    {
        return new Response(
            $status,
            $body,
            array_merge($headers, array('Content-Type' => 'text/html; charset=utf-8')),
            $version,
            $reason
        );
    }
}