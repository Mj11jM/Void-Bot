<?php


namespace VoidBot\Commands\Bot;

use Discord\Parts\Embed\Embed;
use VoidBot\MongoInstance;

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
        $mongo = MongoInstance::getInstance();
        $prefixDB = $mongo->getDB()->voidbot->guildPrefixes;
        $newPrefix = $context['args']['args'][0];
        $prefixDB->findOneAndUpdate(['guild_id' => $context['guild']['id']], ['$set' => ['prefix' => $newPrefix]]);
        $embed = $discord->factory(Embed::class, [
            "color" => $context['color']['green'],
            "author" => [
                "name" => "Prefix Changed"
            ],
            "description" => "Your new prefix is $newPrefix",
        ]);

        $message->channel->sendMessage('', false, $embed);
    }
}