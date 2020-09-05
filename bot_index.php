<?php


use Discord\Discord;
use VoidBot\Commands\CommandRegistrar;
use VoidBot\Functions\MessageHandler;

include './vendor/autoload.php';

$configFile = file_get_contents('./config.json');
$decode = json_decode($configFile, true);

$discord = new Discord([
    'token' => $decode['token'],
    'storeMessages' => true,
    'loadAllMembers' => true,
]);
VoidBot\Discord::setInstance($discord);

$discord->on('ready', function ($discord) {
    $guildCount =  count($discord->guilds);
    echo "{$discord->username} is now online in {$guildCount} guilds!" . PHP_EOL;

    $discord->on('MESSAGE_CREATE', function ($message, $discord) {
        //The next 3 lines gets the specific guild's prefix from the DB
        $mongo = VoidBot\MongoInstance::getInstance();
        $prefixDB = $mongo->getDB()->voidbot->guildPrefixes;
        $guildPrefix = $prefixDB->findOne(['guild_id' => $message->channel->guild->id])->prefix;

        //Checking for Admin Permissions
        if ($message->channel->guild->owner_id === $message->author->id){
            $admin = true;
        } else {
            $admin = false;
            foreach ($message->author->roles as $role) {
                if ($role->permissions->administrator){
                    $admin = true;
                }
            }
        }

        //Check for if the bot owner is the one sending the message.
        $discord->application->owner->id === $message->author->id? $owner = true: $owner = false;

        //Check if the prefix is in the message at the beginning of the message, if so. Pass it on to the command handler
        if (strpos($message->content, $guildPrefix) === 0){
            MessageHandler::getInstance()->getCommand($message, $discord, $guildPrefix, $admin, $owner);
        }
    });

    $discord->on('MESSAGE_REACTION_ADD', function ($message, $discord) {
        //todo
    });
});

$discord->run();