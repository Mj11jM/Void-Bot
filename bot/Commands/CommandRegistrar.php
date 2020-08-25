<?php


namespace VoidBot\Commands;

use VoidBot\Discord;

class CommandRegistrar
{

    public $commands = [
        "VoidBot\Commands\Admin\Test",
    ];

    public function instance() {
        $instance = Discord::getInstance();
        return $instance;
    }

    public function load() {
        foreach ($this->commands as $command){
            $commandClass = new $command();
            $discord = $this->instance();
            $discord->registerCommand(
                $commandClass->trigger,
                $commandClass->command(),
                $commandClass->extra
            );
        }
    }

}