<?php


namespace VoidBot\Commands\Bot;

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

    public function command($message, $discord, $args): void{
        $mongo = MongoInstance::getInstance();
        $prefixDB = $mongo->getDB()->voidbot->guildPrefixes;
        $newPrefix = $args[0];
        $prefixDB->findOneAndUpdate(['guild_id' => $message->channel->guild->id], ['$set' => ['prefix' => $newPrefix]]);
        $message->channel->sendMessage("Prefix has been changed to $newPrefix");
    }
}