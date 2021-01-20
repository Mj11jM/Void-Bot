<?php


namespace VoidBot\Functions;


use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;

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

    public function contextCreation ($message, $discord, $event = false) {
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

        if (!$event) {
            $context['prefix'] = DB::table('guilds')->where('guild_id', '=', $message->author->guild_id)->first('prefix')->prefix;

            //Set all permissions into an easily accessible section
            $context['permissions'] = $message->author->getPermissions();

            $context['guild'] = $message->channel->guild;
            $context['channel'] = $message->channel;
            $context['user'] = $message->author;

            $context['guild_id'] = $message->channel->guild->id;
            $context['channel_id'] = $message->channel->id;
            $context['user_id'] = $message->author->id;
        }


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
                ],
                'command_error' => [
                    "color" => $context['color']['red'],
                    "author" => [
                        "name" => "Error"
                    ],
                    "footer" => [
                        'text' => Carbon::now()->toDateTimeString()
                    ]
                ],
                'command_success' => [
                    "color" => $context['color']['green'],
                    "author" => [
                        "name" => "Success!"
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