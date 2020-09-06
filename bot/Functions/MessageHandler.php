<?php


namespace VoidBot\Functions;


use VoidBot\Commands\Bot\SetPrefix;
use VoidBot\Commands\Misc\Ping;

class MessageHandler
{
    private static $instance = null;

    //This is where commands everyone can use are made
    private $commandNormal = [
        'ping' => Ping::class,
    ];

    //This is where commands requiring specific permissions are made
    private $commandSpecial = [

    ];

    //This is where admin commands are made
    private $commandAdmin = [
        'setprefix' => SetPrefix::class
    ];

    //This is where owner only commands are made
    private $commandOwner = [

    ];

    public function getCommand($message, $discord, $prefix, $permissions)
    {
        if ($message->author->bot || $message->author->id === $discord->user->id) {
            return;
        }


        $args = explode(' ', $message->content);
        $commandName = array_shift($args);
        $command = substr($commandName, strlen($prefix));

        //This filters out any command for other bots that may start with the same prefix, it will not
        //help if both bots have the exact same command
        if (preg_match("/[a-zA-Z]/", str_split($command)[0]) != 1) {
            return;
        }
        
        if (isset($this->commandNormal[$command])) {
            $execute = call_user_func("{$this->commandNormal[$command]}::getInstance");
            $execute->command($message, $discord, $args);

        } elseif (isset($this->commandSpecial[$command])) {
            $execute = call_user_func("{$this->commandSpecial[$command]}::getInstance");
            $execute->command($message, $discord, $args);

        } elseif (isset($this->commandAdmin[$command])) {
            if (!$permissions['admin']) {
                return $message->channel->sendMessage("You require administrator to run this command!");
            }
            $execute = call_user_func("{$this->commandAdmin[$command]}::getInstance");
            $execute->command($message, $discord, $args);

        } elseif (isset($this->commandOwner[$command])) {
            if (!$permissions['owner']) {
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