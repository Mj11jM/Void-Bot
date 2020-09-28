<?php


namespace VoidBot\Commands\Misc;


class Help
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new Help();
        }

        return self::$instance;
    }

    public function command($message, $discord, $args): void{

    }
}