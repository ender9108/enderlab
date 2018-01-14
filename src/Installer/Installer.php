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

        $event->getIO()->write('Creation directory tree');
        $installer->createDirectories();

        $event->getIO()->write('Creation configuration files');
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
                if (true == mkdir($this->rootPath . $directory)) {
                    $this->io->write("\t".'- [<info>OK</info>] Create directory "<info>' . $this->rootPath . $directory . '</info>".');
                } else {
                    $this->io->write("\t".'- [<error>ERR</error>] Cannot create directory "<error>' . $this->rootPath . $directory . '</error>".');
                }
            }
        }
    }

    public function createConfigFiles(bool $verbose = true)
    {
        foreach ($this->config['template-file'] as $source => $dest) {
            if (!is_file($this->rootPath . $dest)) {
                if (true == copy(__DIR__ . '/' . $source, $this->rootPath . $dest)) {
                    $this->io->write("\t".'- [<info>OK</info>] Create file "<info>' . $dest . '</info>".');
                } else {
                    $this->io->write("\t".'- [<error>ERR</error>] Cannot create file "<error>' . $dest . '</error>".');
                }
            }
        }
    }
}
