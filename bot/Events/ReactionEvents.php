<?php


namespace VoidBot\Events;


use VoidBot\Discord;

class ReactionEvents
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new ReactionEvents();
        }

        return self::$instance;
    }

    public function events ($discord): void{
        $discord->on('MESSAGE_REACTION_ADD', function ($message, $discord) {

        });
        $discord->on('MESSAGE_REACTION_REMOVE', function ($message, $discord) {

        });
        $discord->on('MESSAGE_REACTION_REMOVE_ALL', function ($message, $discord) {

        });
        $discord->on('MESSAGE_REACTION_REMOVE_EMOJI', function ($message, $discord) {

        });
    }





}