<?php


namespace VoidBot\Commands\Roles;


use VoidBot\Functions\ArgumentParser;

class Role
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new Role();
        }

        return self::$instance;
    }

    public function command($message, $discord, $args): void{
        switch ($args) {
            case "add":
                break;
            case "remove":
                break;
            default:
                break;
        }
    }
}