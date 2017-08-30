<?php

use EnderLab\Application\App;
use EnderLab\Application\AppFactory;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
    private function makeInstanceApp()
    {
        return AppFactory::create();
    }

    public function testCreateAppObject()
    {
        $app = $this->makeInstanceApp();
        $this->assertInstanceOf(App::class, $app);
    }

    public function testPipeWithInvalidMiddleware()
    {
        $app = $this->makeInstanceApp();
        $this->expectException(\InvalidArgumentException::class);
        $app->pipe('CoucouMiddleware');
    }

    public function testPipeWithInvalidMiddleware()
    {
        $app = $this->makeInstanceApp();
        $this->expectException(\InvalidArgumentException::class);
        $app->pipe('CoucouMiddleware');
    }
}