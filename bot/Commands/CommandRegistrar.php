<?php


namespace VoidBot\Commands;

use VoidBot\Discord;

class CommandRegistrar
{
    function abc() {
        $some = Discord::getInstance();
        dump($some);
    }

}