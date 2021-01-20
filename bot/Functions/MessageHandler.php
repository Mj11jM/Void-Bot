<?php


namespace VoidBot\Functions;


use Carbon\Carbon;
use VoidBot\Commands\Admin\ServerLog;
use VoidBot\Commands\Bot\SetPrefix;
use VoidBot\Commands\Misc\Help;
use VoidBot\Commands\Misc\Ping;
use VoidBot\Commands\Roles\ReactionRole;
use VoidBot\Commands\Roles\SelfRole;

class MessageHandler
{
    private static $instance = null;

    //This is where commands everyone can use are made
    private $commandNormal = [
        'ping' => Ping::class,
        'help' => Help::class,
    ];

    //This is where commands requiring specific permissions are made
    private $commandSpecial = [
        'selfrole' => SelfRole::class,
        'rero' => ReactionRole::class,
        'reactionrole' => ReactionRole::class,
        'reactionroles' => ReactionRole::class,
    ];

    //This is where admin commands are made
    private $commandAdmin = [
        'setprefix' => SetPrefix::class,
        'log' => ServerLog::class
    ];

    //This is where owner only commands are made
    private $commandOwner = [

    ];

    public function getCommand($message, $discord, $context)
    {

        $args = explode(' ', $message->content);
        $parser = ArgumentParser::getInstance();
        $commandName = array_shift($args);
        $command = strtolower(substr($commandName, strlen($context['prefix'])));
        $context['args'] = $parser->parser($args);

        //This filters out any command for other bots that may start with the same prefix, it will not
        //help if both bots have the exact same command
        if (preg_match("/[a-zA-Z]/", str_split($command)[0]) != 1) {
            return;
        }


        if (isset($this->commandNormal[$command])) {
            $execute = call_user_func("{$this->commandNormal[$command]}::getInstance");
            $execute->command($message, $discord, $context);

        } elseif (isset($this->commandSpecial[$command])) {
            $allowed = false;
            $execute = call_user_func("{$this->commandSpecial[$command]}::getInstance");
            //Compare required to actual roles/permissions
            foreach ($execute->requiredRoles as $role) {
                if ($context['permissions'][$role]){
                    $allowed = true;
                }
            }

            if ($allowed) {
                $execute->command($message, $discord, $context);
            } else {
                $embed = $context['embed']['type']['perm_error'];
                $embed['description'] = "You don't have sufficient permissions to use this command, you need at least one of these permissions:\n";
                foreach ($execute->requiredRoles as $role) {
                    $embed['description'] .= $role . PHP_EOL;
                }
                $context['channel']->sendMessage("", false, $embed);
            }

        } elseif (isset($this->commandAdmin[$command])) {
            if (!$context['permissions']['administrator']) {
                $embed = $context['embed']['type']['perm_error'];
                $embed['description'] = " You require administrator to run this command!";
                return $message->channel->sendMessage("", false, $embed);
            }
            $execute = call_user_func("{$this->commandAdmin[$command]}::getInstance");
            $execute->command($message, $discord, $context);

        } elseif (isset($this->commandOwner[$command])) {
            if (!$context['permissions']['owner']) {
                return $message->channel->sendMessage("This command is for the bot owner only!");
            }
            $execute = call_user_func("{$this->commandOwner[$command]}::getInstance");
            $execute->command($message, $discord, $context);

        } else {
            $embed = [
                "color" => $context['color']['red'],
                "author" => [
                    "name" => "Command not found"
                ],
                "description" => "Command \"**$command**\" was not found, please check your spelling and try again.",
                "footer" => [
                    'text' => Carbon::now()->toDateTimeString()
                ]
            ];
            $message->channel->sendMessage("", false, $embed);
        }
    }

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new MessageHandler();
        }

        return self::$instance;
    }

}