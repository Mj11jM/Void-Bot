<?php


namespace VoidBot\Events;


use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
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
        $discord->on('MESSAGE_REACTION_ADD', function ($emoji, $discord) {
            if ($emoji->emoji->name === "🔁") {

            }
        });
        $discord->on('MESSAGE_REACTION_REMOVE', function ($message, $discord) {

        });
        $discord->on('MESSAGE_REACTION_REMOVE_ALL', function ($message, $discord) {

        });
        $discord->on('MESSAGE_REACTION_REMOVE_EMOJI', function ($message, $discord) {

        });
    }





}