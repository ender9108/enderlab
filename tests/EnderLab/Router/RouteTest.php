<?php

namespace Tests\EnderLab\MiddleEarth;

use EnderLab\MiddleEarth\Router\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testGetter()
    {
        $route = new Route('/{id:\\d+}', function () {
        }, 'GET', 'test_route');
        $path = $route->getPath();
        $this->assertSame('/{id:\\d+}', $path);
    }
}
