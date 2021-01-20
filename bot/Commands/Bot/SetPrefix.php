<?php


namespace VoidBot\Commands\Bot;

use Discord\Parts\Embed\Embed;
use Illuminate\Database\Capsule\Manager as DB;

class SetPrefix
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new SetPrefix();
        }

        return self::$instance;
    }

    public function command($message, $discord, $context): void{
        $newPrefix = $context['args']['args'][0];
        $tooLong = strlen($newPrefix) > 4 ;
        $tooShort = strlen($newPrefix) < 1;
        if (!$tooLong && !$tooShort) {
            DB::table('guilds')->where('guild_id', '=', $message->author->guild_id)->update(['prefix' => $newPrefix]);
            $embed = [
                "color" => $context['color']['green'],
                "author" => [
                    "name" => "Prefix Changed"
                ],
                "description" => "Your new prefix is $newPrefix",
            ];

            $message->channel->sendMessage('', false, $embed);

        } else if ($tooShort) {
            $embed = [
                "color" => $context['color']['red'],
                "author" => [
                    "name" => "Prefix Too Short"
                ],
                "description" => "Prefix can't be empty!!",
            ];

            $message->channel->sendMessage('', false, $embed);
        } else {
            $embed = [
                "color" => $context['color']['red'],
                "author" => [
                    "name" => "Prefix Too Long"
                ],
                "description" => "Prefix `$newPrefix` is too long! Prefix maximum length is 4 characters long!",
            ];

            $message->channel->sendMessage('', false, $embed);
        }

    }
}