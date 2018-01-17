<?php
namespace EnderLab\MiddleEarth\Http;

use GuzzleHttp\Psr7\Response;

class JsonResponse extends Response
{
    public function __construct(
        int $status = 200,
        $body = null,
        array $headers = [],
        bool $isJson = false,
        string $version = '1.1',
        string $reason = null
    )
    {
        if (false == $isJson) {
            $body = $this->jsonEncode($body);
        }

        $headers['Content-Type'] = 'application/json';

        parent::__construct($status, $headers, $body, $version, $reason);
    }

    private function jsonEncode($body)
    {
        $json = json_encode($body, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(json_last_error_msg());
        }

        return $json;
    }
}