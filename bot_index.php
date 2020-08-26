<?php


use Discord\Discord;
use VoidBot\Commands\CommandRegistrar;
use VoidBot\Functions\MessageHandler;

include './vendor/autoload.php';

$configFile = file_get_contents('./config.json');
$decode = json_decode($configFile, true);

$discord = new Discord([
    'token' => $decode['token'],
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
        //Check if the prefix is in the message at the beginning of the message, if so. Pass it on to the command handler
        if (strpos($message->content, $guildPrefix) === 0){
            MessageHandler::getInstance()->getCommand($message, $discord, $guildPrefix);
        }
    });
});

$discord->run();