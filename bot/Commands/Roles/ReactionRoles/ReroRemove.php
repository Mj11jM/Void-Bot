<?php


namespace VoidBot\Commands\Roles\ReactionRoles;


use VoidBot\MySQLInstance;

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
        $db = MySQLInstance::getInstance()->getDB();
        $data = Array ("login" => "admin",
            "firstName" => "John",
            "lastName" => 'Doe'
        );
        try {
            $id = $db->insert('users', $data);
        } catch (\Exception $e) {
            dump($e);
        }

        dump($db->get_connection_stats());
        dump("Reaction Role Remove!");
    }

}