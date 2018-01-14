<?php

namespace EnderLab\MiddleEarth\Installer;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Script\Event;

class Installer
{
    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Composer
     */
    private $composer;

    private $config;

    private $rootPath;

    private static $logo = '
______________________________________________________________________________________________________

 /$$      /$$ /$$       /$$       /$$ /$$           /$$$$$$$$                       /$$     /$$      
| $$$    /$$$|__/      | $$      | $$| $$          | $$_____/                      | $$    | $$      
| $$$$  /$$$$ /$$  /$$$$$$$  /$$$$$$$| $$  /$$$$$$ | $$        /$$$$$$   /$$$$$$  /$$$$$$  | $$$$$$$ 
| $$ $$/$$ $$| $$ /$$__  $$ /$$__  $$| $$ /$$__  $$| $$$$$    |____  $$ /$$__  $$|_  $$_/  | $$__  $$
| $$  $$$| $$| $$| $$  | $$| $$  | $$| $$| $$$$$$$$| $$__/     /$$$$$$$| $$  \__/  | $$    | $$  \ $$
| $$\  $ | $$| $$| $$  | $$| $$  | $$| $$| $$_____/| $$       /$$__  $$| $$        | $$ /$$| $$  | $$
| $$ \/  | $$| $$|  $$$$$$$|  $$$$$$$| $$|  $$$$$$$| $$$$$$$$|  $$$$$$$| $$        |  $$$$/| $$  | $$
|__/     |__/|__/ \_______/ \_______/|__/ \_______/|________/ \_______/|__/         \___/  |__/  |__/
______________________________________________________________________________________________________
';

    public static function postCreateProject(Event $event)
    {
        $event->getIO()->write('<info>' . self::$logo . '</info>');
        $installer = new self($event->getIO(), $event->getComposer());

        $event->getIO()->write('<info>Creation directory tree</info>');
        $installer->createDirectories();
        $installer->createConfigFiles();
    }

    public static function event(Event $event)
    {
        $event->getIO()->write('<info>' . $event->getName() . ' - Configuration MiddleEarth !!</info>');
        $event->getIO()->write('<info>' . $event->getName() . ' - Configuration MiddleEarth !!</info>');
        $installer = new self($event->getIO(), $event->getComposer());

        switch ($event->getName()) {
            case 'post-install-cmd':
                $event->getIO()->info('Event post-install-cmd');
                break;
            case 'post-update-cmd':
                $event->getIO()->info('Event post-update-cmd');
                break;
        }
    }

    public function __construct(IOInterface $io, Composer $composer)
    {
        $this->io = $io;
        $this->composer = $composer;
        $this->rootPath = rtrim(realpath(dirname(Factory::getComposerFile())), '/').'/';
        $this->config = include __DIR__ . '/config/config.php';
    }

    public function createDirectories(bool $verbose = true)
    {
        foreach ($this->config['directories'] as $directory) {
            if (!is_dir($this->rootPath . $directory)) {
                mkdir($this->rootPath . $directory);

                if (true === $verbose) {
                    $this->io->write("\t".'- Create directory "' . $this->rootPath . $directory . '".'."\t\t\t\t".'[<info>OK</info>]');
                }
            }
        }
    }

    public function createConfigFiles(bool $verbose = true)
    {
        foreach ($this->config['template-file'] as $source => $dest) {
            if (!is_file($this->rootPath . $dest)) {
                copy(__DIR__ . '/' . $source, $this->rootPath . $dest);

                if (true === $verbose) {
                    $this->io->write("\t".'- Create file "' . $dest . '".'."\t\t\t\t".'[<info>OK</info>]');
                }
            }
        }
    }
}
