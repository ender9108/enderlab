<?php

namespace EnderLab\Installer;

use Composer\Script\Event;

class Events
{
    public static function postInstall(Event $event)
    {
        $event->getIO()->write('Test message "post install"');
    }

    public static function postUpdatel(Event $event)
    {
        $event->getIO()->write('Test message "post update"');
    }
}
