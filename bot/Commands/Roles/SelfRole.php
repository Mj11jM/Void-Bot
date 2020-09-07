<?php


namespace VoidBot\Commands\Roles;


class SelfRole
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new SelfRole();
        }

        return self::$instance;
    }

    public function command($message, $discord, $context): void{
        if (!$context['permissions']['admin'] || !$context['permissions']['manage_roles']) {
            $embed = $context['embed']['type']['perm_error'];
            $embed['description'] = "You don't have sufficient permissions to use this command, you need at least **manage_roles**";
            $context['channel']->sendMessage("", false, $embed);
        }

    }

}