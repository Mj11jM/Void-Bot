<?php


namespace VoidBot\Commands\Roles\ReactionRoles;



use Discord\Discord;
use Discord\Parts\Channel\Message;
use Illuminate\Database\Capsule\Manager;
use VoidBot\MySQLInstance;
use function Emoji\detect_emoji;

class ReroAdd
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new ReroAdd();
        }

        return self::$instance;
    }

    public function addReactionRole(Message $message, Discord $discord, $context) {
        //Get rid of the 'add' in the command arguments
        array_shift($context['args']['args']);
        //Group into groups of 2 to do emoji/role pairing
        $args = array_chunk($context['args']['args'], 2);
        //Make an array of key value pairs for roleID=>Role Name
        $roleList = [];
        foreach ($message->channel->guild->roles as $role) {
            $roleList[$role->id] = $role->name;
        }

        $reroList = [];
        //Go through each grouping and get the location key
        foreach ($args as $key => $group){
            //Go through each individual item in the grouping
            foreach ($group as $text){
                //Detect if it's an emote and set the emote
                //Example of custom discord emotes (<:mj11jmLove:656755804569075713>, <a:headdesk:444981621033009153>)
                if (!empty(detect_emoji($text)) || str_starts_with($text, "<:") || str_starts_with($text, '<a:')){
                    $reroList[$key]['emoji'] = $text;
                } elseif (in_array($text, $roleList)) {
                    $reroList[$key]['role_name'] = $text;
                    $roleID = array_search($text, $roleList);
                    $reroList[$key]['role_id'] = (string) $roleID;
//                    dump((array) $message->channel->guild->roles[$roleID]);
                } else {
                    $embed = $context['embed']['type']['command_error'];
                    $embed['description'] = "An error occurred while fetching emotes and roles. Please check groupings and remove spaces in role names.";
                    return $context['channel']->sendMessage('', false, $embed);
                }
            }
        }

        //Get the message just prior to the one activating this command
         $message->channel->getMessageHistory(['before' => $message->id, 'limit' => 1])
            ->then(function ($messageItem) use ($reroList, $context, $message) {
                $reactedMessage = null;
                //Iterate through the returned message(s) and get the item
                foreach ($messageItem as $messageProper) {
                    $reactedMessage = $messageProper;
                    //For the length of the reaction list add the emojis
                    foreach ($reroList as $setup) {
                        //Pre-set the emoji to manipulate it
                        $emote = $setup['emoji'];
                        //I need to remove the < and > from the emoji to apply it to the message
                        if (strpos($setup['emoji'], '<') === 0) {
                            $emote = str_ireplace(['<', '>'], "", $setup['emoji']);
                        };
                        //React
                        $messageProper->react($emote);
                    }
                }
                foreach ($reroList as $list) {
                    try {
                        Manager::table('reaction_roles')->insert(
                            [
                                "guild_id" => $message->channel->guild->id,
                                "message_id" => $reactedMessage->id,
                                "emoji" => $list['emoji'],
                                "role_id" => $list["role_id"]
                            ]
                        );
                    } catch (\Exception $e) {
                        $embed = $context['embed']['type']['command_error'];
                        $embed['description'] = "There was an issue setting up the reaction role for {$list['emoji']}. Voidbot either doesn't have access to it, or there was error with the database. Please try again.";
                        $context['channel']->sendMessage('', false, $embed);
                        return;
                    }

                }
                //If I made it this far, it worked. If I didn't.... well
                $embed = $context['embed']['type']['command_success'];
                $embed['description'] = "Reaction Role setup has been completed!";
                $context['channel']->sendMessage('', false, $embed);
            });
    }

}