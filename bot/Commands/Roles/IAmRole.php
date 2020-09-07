<?php


namespace VoidBot\Commands\Roles;


class IAmRole
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new IAmRole();
        }

        return self::$instance;
    }

    public function command($message, $discord, $context): void{

    }

}