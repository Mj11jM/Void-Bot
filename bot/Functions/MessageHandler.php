<?php


namespace VoidBot\Functions;


use VoidBot\Commands\Admin\Test;

class MessageHandler
{
    private static $instance = null;

    private $commandNormal = [
        'ping' => Test::class
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