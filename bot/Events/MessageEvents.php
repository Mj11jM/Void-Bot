<?php


namespace VoidBot\Events;



use VoidBot\Functions\ContextCreator;

class MessageEvents
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new MessageEvents();
        }

        return self::$instance;
    }

    public function events ($discord): void{
        $discord->on('MESSAGE_DELETE', function ($message, $discord) {
            //todo
        });
        $discord->on('MESSAGE_UPDATE', function ($newMessage, $discord, $oldMessage) {
//            $context = ContextCreator::getInstance()->contextCreation($newMessage, $discord);
//            dump($context);
        });
        $discord->on('MESSAGE_DELETE_BULK', function ($message, $discord) {
            //todo
        });
    }



}