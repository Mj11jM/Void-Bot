<?php


namespace VoidBot\Events;


use VoidBot\Discord;

class ReactionEvents
{
    private static $instance = null;

    private $discord;

    private function __construct()
    {
        $this->discord = Discord::getInstance();
    }
    public function events () {
        $this->discord->on('MESSAGE_REACTION_ADD', function ($message, $discord) {
            dump($message);
        });
        $this->discord->on('MESSAGE_REACTION_REMOVE', function ($message, $discord) {
            dump($message);
        });
        $this->discord->on('MESSAGE_REACTION_REMOVE_ALL', function ($message, $discord) {
            dump($message);
        });
        $this->discord->on('MESSAGE_REACTION_REMOVE_EMOJI', function ($message, $discord) {
            dump($message);
        });
    }

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new ReactionEvents();
        }

        return self::$instance;
    }



}