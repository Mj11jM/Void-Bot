<?php


namespace VoidBot\Commands\Roles\ReactionRoles;


class ReroRemove
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new ReroRemove();
        }

        return self::$instance;
    }

    public function removeReactionRole($message, $discord, $context) {
        dump("Reaction Role Remove!");
    }

}