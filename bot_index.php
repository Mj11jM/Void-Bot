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


$discord->on('ready', function ($discord) {
   echo 'Bot is ready' . PHP_EOL;
   VoidBot\Discord::setInstance($discord);
    $commands = new CommandRegistrar();
    $commands->abc();
});

$discord->registerCommand('ping', function($message) {
    $authorID = $message->author->id;
    if ($authorID != 91098889796481024) {
        return $message->channel->sendMessage('Not Poodle Enough!');
    }
    $message->channel->sendMessage('<@'.$message->author->id . '> pong');
});



$discord->run();