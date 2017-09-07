<?php
namespace Tests\EnderLab;

use EnderLab\Router\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testGetUrl()
    {
        $route = new Route('/:id', function () {
        }, 'GET', 'test_route', ['id' => '\\d+']);
        $url = $route->getUrl(['id' => 2]);
        $this->assertNotEmpty($url);
    }

    public function testMatchUrl()
    {
        $route = new Route('/:id', function () {
        }, 'GET', 'test_route');
        $result = $route->match('/1');
        $this->assertSame(true, $result);
    }
}
