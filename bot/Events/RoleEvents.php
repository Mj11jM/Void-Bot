<?php


namespace VoidBot\Events;


class RoleEvents
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new RoleEvents();
        }

        return self::$instance;
    }

    public function events($discord): void{
        $discord->on("ROLE_CREATE", function ($role, $discord) {
            //todo
        });
        $discord->on("ROLE_UPDATE", function ($role, $discord, $oldRole) {
            //todo
        });
        $discord->on("ROLE_DELETE", function ($role, $discord) {
            //todo
        });
    }

}