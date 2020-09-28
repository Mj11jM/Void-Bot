<?php


namespace VoidBot\Commands\Roles\ReactionRoles;


class ReroList
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new ReroList();
        }

        return self::$instance;
    }

    public function listReactionRoles($message, $discord, $context) {

    }

}