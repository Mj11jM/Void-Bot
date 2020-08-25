<?php


namespace VoidBot\Commands\Admin;

use Carbon\Carbon;

class Test
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new Test();
        }

        return self::$instance;
    }

    public function command($message, $discord): void{
            $date = Carbon::now('UTC');
            $diff = $date->diffInMilliseconds($message->timestamp);
            $message->channel->sendMessage("Pong in {$diff}ms!");
    }


}