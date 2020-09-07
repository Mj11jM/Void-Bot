<?php


namespace VoidBot\Commands\Misc;

use Carbon\Carbon;
use Discord\Parts\Embed\Embed;


class Ping
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new Ping();
        }

        return self::$instance;
    }

    public function command($message, $discord, $context): void{
        $date = Carbon::now('UTC');
        $diff = $date->diffInMilliseconds($message->timestamp);
        $embed = [
            'color' => $context['color']['green'],
            'author' => [
                'name' => 'Voidbot Ping test'],
            "title" => "Pong in {$diff}ms!",
        ];
        $message->channel->sendMessage('', false, $embed);
    }


}