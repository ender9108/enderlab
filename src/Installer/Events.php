<?php

namespace EnderLab\Installer;

use Composer\Script\Event;

class Events
{
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
        $event->getIO()->write('Test message "post create project"');
    }
}
