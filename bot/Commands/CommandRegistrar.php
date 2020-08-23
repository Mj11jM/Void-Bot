<?php


namespace VoidBot\Commands;

use VoidBot\Discord;

class CommandRegistrar
{

    public function instance() {

        $instance = Discord::getInstance();
        return $instance;
    }

}