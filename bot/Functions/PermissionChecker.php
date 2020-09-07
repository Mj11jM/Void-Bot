<?php


namespace VoidBot\Functions;


class PermissionChecker
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new PermissionChecker();
        }

        return self::$instance;
    }

    public function permissions($message, $discord) {
        //Checking for Permissions
        $permissions = [
            'admin' => false,
            'owner' => false,
            'manage_channels' => false,
            'kick_members' => false,
            'ban_members' => false,
            'manage_roles' => false,
        ];
        //If the guild owner is the one running the command, set them as admin and ignore the rest of the role checks
        if ($message->channel->guild->owner_id === $message->author->id){
            $permissions['admin'] = true;
        } else {
            //Since the user isn't the owner, check over each role they have for the permissions we might require
            foreach ($message->author->roles as $role) {
                if ($role->permissions->administrator){
                    $permissions['admin'] = true;
                }
                if ($role->permissions->manage_channels){
                    $permissions['manage_channels'] = true;
                }
                if ($role->permissions->manage_roles){
                    $permissions['manage_roles'] = true;
                }
                if ($role->permissions->kick_members){
                    $permissions['kick_members'] = true;
                }
                if ($role->permissions->ban_members){
                    $permissions['ban_members'] = true;
                }
            }
        }

        //Check for if the bot owner is the one sending the message.
        if($discord->application->owner->id === $message->author->id) {
            $permissions['owner'] = true;
        }

        return $permissions;
    }

}