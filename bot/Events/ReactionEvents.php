<?php


namespace VoidBot\Events;


use Carbon\Carbon;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Emoji;
use Discord\Parts\WebSockets\MessageReaction;
use Illuminate\Database\Capsule\Manager as DB;
use VoidBot\Discord;
use VoidBot\Functions\EventFunctions\QuoteRepeat;

class ReactionEvents
{
    private static $instance = null;
    private $discord;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new ReactionEvents();
        }

        return self::$instance;
    }


    private function __construct() {
        $this->discord = Discord::getInstance();
    }

    public function events ($discord): void{
        $discord->on('MESSAGE_REACTION_ADD', function (MessageReaction $emoji, $discord) {
            if ($emoji->user->bot) {
                return;
            }
            $emoji->channel->messages->fetch($emoji->message_id)->then(function (Message $message) use ($emoji, $discord) {
                //Is the reaction the quote emote?
                $reaction = $emoji->emoji->id? $emoji->emoji->id: $emoji->emoji->name;
                $activeRero = DB::table('reaction_roles')->where([['message_id', '=', $message->id], ['emoji', '=', $reaction]])->first();
                if ($emoji->emoji->name === "🔁" && empty($activeRero)) {
                    QuoteRepeat::repeatMessage($emoji, $message);
                } elseif ($activeRero) {
                    $message->channel->guild->roles->fetch($activeRero->role_id)->done(function ($role) use ($emoji) {
                        $emoji->member->addRole($role);
                    });
                }

            }, function ($e)
            {
                $this->discord->logger->error('Error In Quote:', $e);
            });
        });
        $discord->on('MESSAGE_REACTION_REMOVE', function (MessageReaction $reaction, $discord) {
            if($reaction->user->bot){
                return;
            }
            $emoji = $reaction->emoji->id? $reaction->emoji->id: $reaction->emoji->name;
            $activeRero = DB::table('reaction_roles')->where([['message_id', '=', $reaction->message_id], ['emoji', '=', $emoji]])->first();
            if (!$activeRero) {
                return;
            }
            $reaction->member->removeRole($activeRero->role_id);
        });
        $discord->on('MESSAGE_REACTION_REMOVE_ALL', function ($message, $discord) {

        });
        $discord->on('MESSAGE_REACTION_REMOVE_EMOJI', function ($message, $discord) {

        });
    }





}