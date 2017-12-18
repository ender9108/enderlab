<?php 
namespace EnderLab\Initializer;

use Composer\Script\Event;

class Initializer {
    public static function postInstall(Event $event)
    {
        $composer = $event->getComposer();
        // do stuff
        print_r($composer);
    }
}