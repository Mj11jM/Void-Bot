<?php


namespace VoidBot\Events;


use Discord\Parts\Guild\Guild;
use VoidBot\Discord;

class EventsCore
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new EventsCore();
        }

        return self::$instance;
    }

    private $discord;

    private function __construct()
    {
        $this->discord = Discord::getInstance();
    }

    private $eventList = [
        ChannelEvents::class,
        GuildEvents::class,
        MessageEvents::class,
        ReactionEvents::class,
        RoleEvents::class,
    ];

    public function eventStarter () {
        foreach ($this->eventList as $events) {
            $event = call_user_func("{$events}::getInstance");
            $event->events($this->discord);
        }
    }


}