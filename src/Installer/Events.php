<?php

namespace EnderLab\Installer;

use Composer\IO\IOInterface;
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
        'tests',
        'tmp',
        'tmp/log',
        'tmp/cache'
    ];

    private static $templateFile = [
        'template/config.php' => 'config/config.php',
        'template/index.php'  => 'public/index.php',
        'template/router.php' => 'config/router.php'
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
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir') . '/';
        $rootDir = $vendorDir . '../';

        // Step 1: build directory tree
        self::createDirectories($event->getIO(), $rootDir, true);

        // Step 2: create config file
        self::createConfigFiles($event->getIO(), $rootDir, true);

        $event->getIO()->write('Create project down.');
    }

    private static function createDirectories(IOInterface $io, string $rootDir, bool $verbose = true)
    {
        foreach (self::$directories as $directory) {
            mkdir($rootDir . $directory);

            if (true === $verbose) {
                $io->write('Create directory "' . $rootDir . $directory . '".');
            }
        }
    }

    private static function createConfigFiles(IOInterface $io, $rootDir, bool $verbose = true)
    {
        foreach (self::$templateFile as $source => $dest) {
            copy(__DIR__ .'/'. $source, $rootDir . $dest);

            if (true === $verbose) {
                $io->write('Create file "' . $dest . '".');
            }
        }
    }
}
