<?php


namespace VoidBot\Commands\Roles;


use VoidBot\Commands\Roles\ReactionRoles\ReroAdd;
use VoidBot\Commands\Roles\ReactionRoles\ReroList;
use VoidBot\Commands\Roles\ReactionRoles\ReroRemove;

class ReactionRole
{
    private static $instance = null;
    public $requiredRoles = ['administrator', 'manage_roles'];

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new ReactionRole();
        }

        return self::$instance;
    }

    public function command($message, $discord, $context): void{
        $arg = $context['args']['args'][0];
        $addOptions = ['add', 'a', 'create'];
        $removeOptions = ['remove', 'delete', 'rm'];
        $listOptions = ['list', 'li', 'ls'];
        switch ($arg) {
            case in_array($arg, $addOptions):
                ReroAdd::getInstance()->addReactionRole($message, $discord, $context);
                break;
            case in_array($arg, $removeOptions):
                ReroRemove::getInstance()->removeReactionRole($message, $discord, $context);
                break;
            case in_array($arg, $listOptions):
                ReroList::getInstance()->listReactionRoles($message, $discord, $context);
                break;
            default:
                dump($arg);
        }
    }

}