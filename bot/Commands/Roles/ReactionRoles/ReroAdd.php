<?php


namespace VoidBot\Commands\Roles\ReactionRoles;



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

    public function addReactionRole($message, $discord, $context) {
        array_shift($context['args']['args']);
        $args = array_chunk($context['args']['args'], 2);
        $roleList = [];
        foreach ($message->channel->guild->roles as $role) {
            $roleList[] = mb_strtolower($role->name);
        }

        $reroList = [];
        foreach ($args as $key => $group){
            foreach ($group as $text){
                if (!empty(detect_emoji($text)) || str_starts_with($text, "<")){
                    $reroList[$key]['emoji'] = $text;
                } elseif (in_array(mb_strtolower($text), $roleList)) {
                    $reroList[$key]['role_name'] = $text;
                } else {

                }
            }
        }
        dump($reroList);


    }

}