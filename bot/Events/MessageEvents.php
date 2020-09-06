<?php


namespace VoidBot\Events;


use VoidBot\Discord;

class MessageEvents
{
    private static $instance = null;

    private $discord;

    private function __construct()
    {
        $this->discord = Discord::getInstance();
    }

    public function events () {
        $this->discord->on('MESSAGE_DELETE', function ($message, $discord) {
            //todo
        });
        $this->discord->on('MESSAGE_UPDATE', function ($message, $discord) {
            //todo
        });
        $this->discord->on('MESSAGE_DELETE_BULK', function ($message, $discord) {
            //todo
        });
    }

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new MessageEvents();
        }

        return self::$instance;
    }

}