<?php


namespace VoidBot\Events;


class ChannelEvents
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new ChannelEvents();
        }

        return self::$instance;
    }

    public function events($discord): void{
        $discord->on("CHANNEL_CREATE", function () {
            //todo
        });
        $discord->on("CHANNEL_UPDATE", function () {
            //todo
        });
        $discord->on("CHANNEL_DELETE", function () {
            //todo
        });
        $discord->on("CHANNEL_PINS_UPDATE", function () {
            //todo
        });
    }

}