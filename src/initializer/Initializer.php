<?php 
namespace EnderLab\Initializer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class Initializer implements PluginInterface, EventSubscriberInterface {
    private $composer;
    private $io;

    public function __construct(Composer $composer, IOInterface $io) {
        $this->composer = $composer;
        $this->io = $io;

        print_r($composer);
        print_r($io);
    }

    public static function test() {

    }
}