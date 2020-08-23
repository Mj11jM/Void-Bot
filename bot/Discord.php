<?php


namespace VoidBot;


class Discord
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            die();
        }

        return self::$instance;
    }

    public static function setInstance($discord) {
        self::$instance = $discord;
    }
}