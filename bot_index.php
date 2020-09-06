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

        //Checking for Permissions
        $permissions = [
            'admin' => false,
            'owner' => false,
            'manage_channels' => false,
            'kick_members' => false,
            'ban_members' => false,
            'manage_roles' => false,
        ];
        //If the guild owner is the one running the command, set them as admin and ignore the rest of the role checks
        if ($message->channel->guild->owner_id === $message->author->id){
            $permissions['admin'] = true;
        } else {
            //Since the user isn't the owner, check over each role they have for the permissions we might require
            foreach ($message->author->roles as $role) {
                if ($role->permissions->administrator){
                    $permissions['admin'] = true;
                }
                if ($role->permissions->manage_channels){
                    $permissions['manage_channels'] = true;
                }
                if ($role->permissions->manage_roles){
                    $permissions['manage_roles'] = true;
                }
                if ($role->permissions->kick_members){
                    $permissions['kick_members'] = true;
                }
                if ($role->permissions->ban_members){
                    $permissions['ban_members'] = true;
                }
            }
        }

        //Check for if the bot owner is the one sending the message.
        if($discord->application->owner->id === $message->author->id) {
            $permissions['owner'] = true;
        }

        //Check if the prefix is in the message at the beginning of the message, if so. Pass it on to the command handler
        if (strpos($message->content, $guildPrefix) === 0){
            MessageHandler::getInstance()->getCommand($message, $discord, $guildPrefix, $permissions);
        }
    });

    $events = VoidBot\Events\EventsCore::getInstance();
    $events->eventStarter();

});

$discord->run();