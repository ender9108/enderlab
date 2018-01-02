<?php

namespace EnderLab\Installer;

use Composer\Script\Event;

class Events
{
    private static $directories = [
        'app',
        'bin',
        'config',
        'public',
        'public/js',
        'public/css',
        'tests'
    ];

    public static function postInstall(Event $event)
    {
        $event->getIO()->write('Test message "post install"');
    }

    public static function postUpdate(Event $event)
    {
        $event->getIO()->write('Test message "post update"');
    }

    public static function postCreateProject(Event $event)
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir').'/';
        $rootDir = $vendorDir.'../';

        foreach (self::$directories as $directory) {
            mkdir($rootDir.$directory);
            $event->getIO()->write('Create directory "'.$rootDir.$directory.'".');
        }

        $event->getIO()->write('Create project down.');
    }
}
