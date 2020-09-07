<?php


namespace VoidBot\Functions;


use Carbon\Carbon;
use VoidBot\MongoInstance;

class ContextCreator
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new ContextCreator();
        }

        return self::$instance;
    }

    public function contextCreation ($message, $discord) {
        $context = [
            "color" => [
                'red' => '11143690',
                'green' => '2470178',
                'blue' => '660148',
                'light_green' => '5293377',
                'orange' => '15630098',
                'yellow' => '16774947',
                'light_blue' => '3137267',
            ]
        ];

        //The next 3 lines gets the specific guild's prefix from the DB
        $mongo = MongoInstance::getInstance();
        $prefixDB = $mongo->getDB()->voidbot->guildPrefixes;
        $context['prefix'] = $prefixDB->findOne(['guild_id' => $message->channel->guild->id])->prefix;

        //Set all permissions into an easily accessible section
        $permission = \VoidBot\Functions\PermissionChecker::getInstance();
        $context['permissions'] = $permission->permissions($message, $discord);

        $context['guild'] = $message->channel->guild;
        $context['channel'] = $message->channel;
        $context['user'] = $message->author;

        $context['guild_id'] = $message->channel->guild->id;
        $context['channel_id'] = $message->channel->id;
        $context['user_id'] = $message->author->id;


        $context['embed'] = [
            'type' => [
                'perm_error' => [
                    "color" => $context['color']['red'],
                    "author" => [
                        "name" => "Insufficient Permissions"
                    ],
                    "footer" => [
                        'text' => Carbon::now()->toDateTimeString()
        ]
                ]
            ]
        ];

        return $context;
    }

}