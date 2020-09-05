<?php


namespace VoidBot\Functions;


use VoidBot\Commands\Bot\SetPrefix;
use VoidBot\Commands\Misc\Ping;

class MessageHandler
{
    private static $instance = null;
    //This is where I will declare normal/global command. There will be an admin command array later
    private $commandNormal = [
        'ping' => Ping::class,
        'setprefix' => SetPrefix::class
    ];

    private $commandAdmin = [

    ];

    private $commandOwner = [

    ];


    public function getCommand($message, $discord, $prefix, $admin, $owner)
    {
        if ($message->author->bot || $message->author->id === $discord->user->id) {
            return;
        }
        dump($admin);
        $args = explode(' ', $message->content);
        $commandName = array_shift($args);

        $command = substr($commandName, strlen($prefix));
        if (isset($this->commandNormal[$command])) {
            $execute = call_user_func("{$this->commandNormal[$command]}::getInstance");
            $execute->command($message, $discord, $args);
        } elseif (isset($this->commandAdmin[$command])) {
            if (!$admin) {
                return $message->channel->sendMessage("You require administrator to run this command!");
            }
            $execute = call_user_func("{$this->commandAdmin[$command]}::getInstance");
            $execute->command($message, $discord, $args);
        } elseif (isset($this->commandOwner[$command])) {
            if (!$owner) {
                return $message->channel->sendMessage("This command is for the bot owner only!");
            }
            $execute = call_user_func("{$this->commandOwner[$command]}::getInstance");
            $execute->command($message, $discord, $args);
        } else {
            $message->channel->sendMessage("Command was not found, please check your spelling and try again.");
        }
    }

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new MessageHandler();
        }

        return self::$instance;
    }

}