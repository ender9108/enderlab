<?php
namespace EnderLab\Installer;

use Composer\Script\Event;

class Events
{
    public static function postInstall(Event $event) {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        echo("Vendor directory : ".$vendorDir);
    }
}