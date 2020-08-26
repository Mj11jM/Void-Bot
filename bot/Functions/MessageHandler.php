<?php


namespace VoidBot\Functions;


use VoidBot\Commands\Misc\Ping;

class MessageHandler
{
    private static $instance = null;
    //This is where I will declare normal/global command. There will be an admin command array later
    private $commandNormal = [
        'ping' => Ping::class
    ];


    public function getCommand($message, $discord, $prefix)
    {
        if ($message->author->bot || $message->author->id === $discord->user->id) {
            return;
        }

        $args = explode(' ', $message->content);
        $commandName = array_shift($args);

        $command = substr($commandName, strlen($prefix));
        if (isset($this->commandNormal[$command])) {
            $execute = call_user_func("{$this->commandNormal[$command]}::getInstance");
            $execute->command($message, $discord, $args);
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