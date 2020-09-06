<?php


namespace VoidBot\Events;


class EventsCore
{
    private static $instance = null;
    private $eventList = [
        ReactionEvents::class,
        MessageEvents::class
    ];

    public function eventStarter () {
        foreach ($this->eventList as $events) {
            $event = call_user_func("{$events}::getInstance");
            $event->events();
        }
    }

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new EventsCore();
        }

        return self::$instance;
    }
}