<?php

namespace EnderLab\MiddleEarth\PackageManager;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Script\Event;

class ComposerEventManager
{
    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Composer
     */
    private $composer;

    //private $config;

    /**
     * @var string
     */
    private $rootPath;

    /**
     * @var string
     */
    private $composerJsonPath;

    private $composerJson;

    public static function event(Event $event)
    {
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
        $this->composerJsonPath = Factory::getComposerFile();
        $this->composerJson = new JsonFile($this->composerJsonPath);
        $this->rootPath = rtrim(realpath(dirname($this->composerJsonPath)), '/') . '/';

        //$this->config = include __DIR__ . '/config/config.php';
    }

    public function clearCache()
    {
        $directory = $this->rootPath . 'tmp/cache';

        if (!is_dir($directory)) {
            return;
        }

        $rdi = new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS);
        $rii = new \RecursiveIteratorIterator($rdi, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($rii as $filename => $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($filename);
                continue;
            }
            unlink($filename);
        }

        $this->io->write("\t" . '[<info>OK</info>] Clean "<info>tmp/cache</info>".');
    }
}
