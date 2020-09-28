<?php


namespace VoidBot\Events;


use Carbon\Carbon;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use VoidBot\Discord;

class ReactionEvents
{
    private static $instance = null;
    private $discord;
    private $extra;

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
        $discord->on('MESSAGE_REACTION_ADD', function ($emoji, $discord) {

            //Is the reaction the quote emote?
            if ($emoji->emoji->name === "🔁") {
                //This is always empty, I can't be bothered to remove/change this anymore
                if (empty($emoji->message->content)) {
                    //Setting extra to the emoji data because we cannot access it inside the then function otherwise
                    $this->extra = $emoji;
                    //This is the nonsense needed to get a message if you don't have it via cache, which the event doesn't fill
                    $emoji->channel->messages->fetch($emoji->message_id)->then(function (Message $message) {
                        //Reactor is who reacted to the message, message author is the author of the message being
                        //reacted to
                        $reactor = $this->extra->user->username;
                        $messageAuthor = $message->author->username;
                        //$message->embeds is an array object. There shouldn't be anything past 0, and if there is. Well, too bad
                        if (!empty($message->embeds[0])) {

                            //This is basically a direct rip of all the fillable fields for an embed
                            $quotedEmbed = [
                                'title' => $message->embeds[0]->title,
                                "author" => [
                                    "name" => "$reactor quoted $messageAuthor",
                                ],
                                'fields' => [
                                    0 => [
                                        'name' => 'Original Message Link',
                                        'value' => "[Message Link](https://discordapp.com/channels/{$this->extra->guild_id}/{$this->extra->channel_id}/{$message->id})"
                                    ]
                                ],
                                'image' => $message->embeds[0]->image,
                                'description' => $message->embeds[0]->description,
                                'color' => '2470178',
                                'thumbnail' => $message->embeds[0]->thumbnail,
                                'video' => $message->embeds[0]->video,
                                'footer' => [
                                    'text' => Carbon::now()->toDateTimeString(),
                                ]
                            ];

                            //Since we set the author field to the quote message, check if it exists and set it as the first field instead
                            if(!is_null($message->embeds[0]->author->name)) {
                                $quotedEmbed['fields'][] = [
                                    'name' => 'Quoted Embed Author Field:', 'value' => $message->embeds[0]->author->name
                                ];
                            }
                            //Transfer over the existing fields
                            foreach ($message->embeds[0]->fields as $field) {
                                $quotedEmbed['fields'][] = $field;
                            }

                            //Send the message back to the channel it was quoted in
                            $message->channel->sendMessage("", false, $quotedEmbed);
                        } else {
                            //Basic Embed setup
                            $embed = [
                                "author" => [
                                    "name" => "$reactor quoted $messageAuthor)"
                                ],
                                'description' => "[Message Link](https://discordapp.com/channels/{$this->extra->guild_id}/{$this->extra->channel_id}/{$message->id})\n" . $message->content,
                                "color"=> '2470178',
                                "footer" => [
                                    'text' => Carbon::now()->toDateTimeString(),
                                ]
                            ];
                            //Check for any message attachments
                            if (!empty($message->attachments[0])) {
                                $imageCount = 0;
                                foreach ($message->attachments as $attach) {
                                    $imageTypes = ['jpg', 'png', 'gif', 'jpeg'];
                                    //Explode at the period to pop the last item(file type) onto a vairable to check
                                    $explodeURL = explode('.', $attach->url);
                                    $filetype = array_pop($explodeURL);
                                    if (in_array($filetype, $imageTypes) && $imageCount < 1) {
                                        ++$imageCount;
                                        $embed['image']['url'] = $attach->url;
                                    } else {
                                        in_array($filetype, $imageTypes)? ++$imageCount: null;
                                        if (!isset($embed['fields'])) {
                                            $embed['fields'][] = ['name' =>'Attached File Links', 'value' => "[Attached Item $filetype]($attach->url)"];
                                        } else {
                                            foreach ($embed['fields'] as $key => $field) {
                                                if ($field['name'] === 'Attached File Links') {
                                                    $embed['fields'][$key]['value'] .= "\n [Attached Item $filetype]($attach->url)";
                                                }
                                            }
                                        }
                                    }

                                }

                                if($imageCount >= 2) {
                                    $embed['footer']['text'] .= "\nMore than 1 image in quoted message. Only first image displayed";
                                }
                            }
                            $message->channel->sendMessage("", false, $embed);
                        }

                    }, function ($e) {
                        $this->discord->logger->error('Error In Quote:', $e);
                    });
                }

            }
        });
        $discord->on('MESSAGE_REACTION_REMOVE', function ($message, $discord) {

        });
        $discord->on('MESSAGE_REACTION_REMOVE_ALL', function ($message, $discord) {

        });
        $discord->on('MESSAGE_REACTION_REMOVE_EMOJI', function ($message, $discord) {

        });
    }





}