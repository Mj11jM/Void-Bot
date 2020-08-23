<?php


use Discord\DiscordCommandClient;
use VoidBot\Commands\CommandRegistrar;

include './vendor/autoload.php';

$configFile = file_get_contents('./config.json');
$decode = json_decode($configFile, true);



$discord = new DiscordCommandClient([
    'token' => $decode['token'],
    'prefix' => '-',
]);
VoidBot\Discord::setInstance($discord);

$discord->on('ready', function ($discord) {
    $guildCount =  count($discord->guilds);
    echo "{$discord->username} is now online in {$guildCount} guilds!" . PHP_EOL;
    $commands = new CommandRegistrar();
    $commands->instance();
});

$discord->registerCommand('ping', function($message) {
    $authorID = $message->author->id;
    if ($authorID != 91098889796481024) {
        return $message->channel->sendMessage('No!');
    }

    $message->channel->sendMessage('<@'.$message->author->id . '> pong');
});



$discord->run();