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

        $command = substr($args[0], strlen($prefix));
        if (isset($this->commandNormal[$command])) {
            $execute = call_user_func("{$this->commandNormal[$command]}::getInstance");
            $execute->command($message, $discord);
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