<?php


namespace VoidBot\Events;

use Carbon\Carbon;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\User;
use VoidBot\Functions\ContextCreator;
use VoidBot\Functions\LogFinder;

class MessageEvents
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new MessageEvents();
        }

        return self::$instance;
    }

    public function events ($discord): void{
        $discord->on('MESSAGE_DELETE', function ($message, Discord $discord) {
            //Don't fire log events if we can access guild ID this way, as there will be no info
            if (!($message instanceof Message)) {
                return;
            }
            $activeLog = LogFinder::findEventLog($message->author->guild_id, ['message_delete']);
            if (!$activeLog || $message->author->user->bot || $message->author->bot ) {
                return;
            }
            $context = ContextCreator::getInstance()->contextCreation(null, $discord, true);
            foreach ($activeLog as $logChannel) {
                $embed = $context['embed']['type']['command_success'];
                $embed['author'] = [
                    "name" => "Message Deleted"
                ];
                $embed['description'] = "User **{$message->author->username}** `{$message->author->id}` deleted a message";
                $embed['fields'] = [
                    0 => [
                        'name'=> "Deleted Message",
                        'value'=> "$message->content"
                    ]
                ];
                $discord->getChannel($logChannel->channel_id)->sendMessage('', false, $embed);
            }
        });
        $discord->on('MESSAGE_UPDATE', function (Message $newMessage, Discord $discord, $oldMessage) {
            if (is_null($oldMessage) || $newMessage->author->user->bot || $newMessage->author->bot  || (!empty($newMessage->embeds->toArray()) && empty($newMessage->content)) || $newMessage->content === $oldMessage->content) {
                return;
            }
            $activeLog = LogFinder::findEventLog($newMessage->author->guild_id, ['message_update']);
            if (!$activeLog) {
                return;
            }
            $context = ContextCreator::getInstance()->contextCreation(null, $discord, true);
            foreach ($activeLog as $logChannel) {
                $embed = $context['embed']['type']['command_success'];
                $embed['author'] = [
                    "name" => "Message Updated"
                ];
                $embed['fields'] = [
                    0 => [
                        'name'=> "Old Message",
                        'value'=> "$oldMessage->content"
                    ],
                    1 => [
                        'name'=> "New Message",
                        'value'=> "$newMessage->content"
                    ]
                ];
                $discord->getChannel($logChannel->channel_id)->sendMessage('', false, $embed);
            }
        });
        $discord->on('MESSAGE_DELETE_BULK', function ($message, $discord) {
            return;
            $activeLog = LogFinder::findEventLog($message->guild_id, 'message_delete_bulk');
            if (!$activeLog) {
                return;
            }
        });
    }



}