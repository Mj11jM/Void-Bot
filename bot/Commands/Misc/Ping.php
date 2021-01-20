<?php


namespace VoidBot\Commands\Misc;

use Carbon\Carbon;
use Discord\Parts\Channel\Message;
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
        $embed = [
            'color' => $context['color']['green'],
            'author' => [
                'name' => 'Voidbot Ping test'
            ],
            "title" => "🏓",
        ];
        $message->channel->sendMessage('', false, $embed)->then(function ($newMessage) use ($message, $context){
            $received = new Carbon($message->timestamp);
            $diff = $received->diffInMilliseconds($newMessage->timestamp);
            $embed = [
                'color' => $context['color']['green'],
                'author' => [
                    'name' => 'Voidbot Ping test'],
                "title" => "Ping in {$diff}ms!",
            ];
            $newMessage->channel->editMessage($newMessage, '', false, $embed);
        });

    }


}