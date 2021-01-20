#!/usr/bin/env php
<?php


use Discord\Discord;
use VoidBot\Functions\ContextCreator;
use VoidBot\Functions\MessageHandler;
use VoidBot\MySQLInstance;

include './vendor/autoload.php';

$configFile = file_get_contents('./config.json');
$decode = json_decode($configFile, true);

$discord = new Discord([
    'token' => $decode['token'],
    'storeMessages' => true,
    'loadAllMembers' => true,
    'pmChannels' => true,
//    'loggerLevel' => 'debug',
]);
VoidBot\Discord::setInstance($discord);
MySQLInstance::getInstance();

$discord->on('ready', function ($discord) {
    $guildCount =  count($discord->guilds);
    echo "{$discord->username} is now online in {$guildCount} guilds!" . PHP_EOL;

    $discord->updatePresence();

    $discord->on('MESSAGE_CREATE', function ($message, $discord) {
        if ($message->author->bot || $message->author->id === $discord->user->id) {
            return;
        }

        //Check for a message in DMs, if it is and the message isn't from the bot. Reply
        if ($message->channel->is_private) {
            if ($message->author->id === $discord->user->id){
                return;
            } else {
                return $message->channel->sendMessage("DM Commands are currently disabled.");
            }
        }

        //Create easily accessible context data and send it through to the commands.
        $context = ContextCreator::getInstance()->contextCreation($message, $discord);

        //Check if the prefix is in the message at the beginning of the message, if so. Pass it on to the command handler
        if (strpos($message->content, $context['prefix']) === 0){
            MessageHandler::getInstance()->getCommand($message, $discord, $context);
        }
    });

//    Commented out but kept for future
//    $discord->getLoop()->addPeriodicTimer(0.1, function() {
//
//    });

    VoidBot\Events\EventsCore::getInstance()->eventStarter();


});

$discord->run();