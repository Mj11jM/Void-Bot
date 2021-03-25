<?php


namespace VoidBot\Functions\EventFunctions;


use Carbon\Carbon;
use Discord\Parts\Channel\Message;
use Discord\Parts\WebSockets\MessageReaction;

class QuoteRepeat
{

    public static function repeatMessage(MessageReaction $emoji, Message $message) {
        //Reactor is who reacted to the message, message author is the author of the message being
        //reacted to

        $reactor = $emoji->user->username;
        $messageAuthor = $message->author->username;
        //$message->embeds is an array object. There shouldn't be anything past 0, and if there is. Well, too bad
        $embedStart = [
            "author" => [
                "name" => "$reactor quoted $messageAuthor",
                "icon_url" => $emoji->user->avatar
            ],
            'color' => '2470178',
            'footer' => [
                'text' => Carbon::now()->toDateTimeString(),
            ],
            'fields' => [
            0 => [
                'name' => 'Original Message Link',
                'value' => "[Message Link](https://discordapp.com/channels/{$emoji->guild_id}/{$emoji->channel_id}/{$message->id})"
            ]
        ],
        ];
        if (!empty($message->embeds[0])) {
            $embedStart['title'] = $message->embeds[0]->title;
            $embedStart['image'] = $message->embeds[0]->image;
            $embedStart['description'] = $message->embeds[0]->description;
            $embedStart['thumbnail'] = $message->embeds[0]->thumbnail;
            $embedStart['video'] = $message->embeds[0]->video;

            //Since we set the author field to the quote message, check if it exists and set it as the first field instead
            if (!is_null($message->embeds[0]->author->name)) {
                $embedStart['fields'][] = [
                    'name' => 'Quoted Embed Author Field:', 'value' => $message->embeds[0]->author->name
                ];
            }
            //Transfer over the existing fields
            foreach ($message->embeds[0]->fields as $field) {
                $embedStart['fields'][] = $field;
            }

            //Send the message back to the channel it was quoted in
            $message->channel->sendMessage("", false, $embedStart);
        } else {
            $embedStart['description'] = $message->content;
            //Check for any message attachments
            if (!empty($message->attachments[0])) {
                $imageCount = 0;
                foreach ($message->attachments as $attach) {
                    $imageTypes = ['jpg', 'png', 'gif', 'jpeg'];
                    //Explode at the period to pop the last item(file type) onto a variable to check
                    $explodeURL = explode('.', $attach->url);
                    $filetype = array_pop($explodeURL);
                    if (in_array($filetype, $imageTypes) && $imageCount < 1) {
                        ++$imageCount;
                        $embedStart['image']['url'] = $attach->url;
                    } else {
                        in_array($filetype, $imageTypes) ? ++$imageCount : null;
                        if (!isset($embedStart['fields'])) {
                            $embedStart['fields'][] = ['name' => 'Attached File Links', 'value' => "[Attached Item $filetype]($attach->url)"];
                        } else {
                            foreach ($embedStart['fields'] as $key => $field) {
                                if ($field['name'] === 'Attached File Links') {
                                    $embedStart['fields'][$key]['value'] .= "\n [Attached Item $filetype]($attach->url)";
                                }
                            }
                        }
                    }
                }

                if ($imageCount >= 2) {
                    $embedStart['footer']['text'] .= "\nMore than 1 image in quoted message. Only first image displayed";
                }
            }
            $message->channel->sendMessage("", false, $embedStart);
        }
    }
}