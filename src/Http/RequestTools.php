<?php

namespace EnderLab\MiddleEarth\Http;

use Psr\Http\Message\ServerRequestInterface;

class RequestTools
{
    public static function buildUrl(ServerRequestInterface $request): string
    {
        $url = $request->getUri()->getScheme() . '://';
        $url .= $request->getUri()->getHost();
        $url .= ('' === $request->getUri()->getPort() ? '' : $request->getUri()->getPort());
        $url .= $request->getUri()->getPath();
        $url .= ('' === $request->getUri()->getQuery()) ? '' : '?' . $request->getUri()->getQuery();

        return $url;
    }
}
