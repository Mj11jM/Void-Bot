<?php


namespace VoidBot\Commands\Admin;


use Discord\Discord;
use Discord\Parts\Channel\Message;
use Illuminate\Database\Capsule\Manager as DB;
use VoidBot\Functions\LogFinder;

class ServerLog
{
    private static $instance = null;
    public $requiredRoles = ['administrator'];

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new ServerLog();
        }

        return self::$instance;
    }

    public function command(Message $message, Discord $discord, $context): void{
        $arg = strtolower($context['args']['args'][0]);
        $option = !empty($context['args']['args'][1])? strtolower($context['args']['args'][1]): null;
        $activeLog = LogFinder::findLog($message->author->guild_id, $message->channel_id);
        $events = [
            'channel_create', 'channel_update', 'channel_delete', 'channel_pins_update',  'ban_add',
            'ban_remove', 'member_add', 'member_update', 'member_remove', 'role_create',
            'role_update', 'role_delete', 'guild_update', 'invite_create', 'invite_delete',
            'message_update', 'message_delete', 'message_delete_bulk', 'presence_update',
            'voice_server_update', 'voice_state_update', 'role_add', 'role_removed', 'nickname_change',
            "name_change", 'discriminator_change', 'avatar_change'];
        $starts = ['add', 'start', 'enable'];
        $stops = ['disable', 'stop', 'remove'];
        $query = ['guild_id' => $message->author->guild_id, 'channel_id' => $message->channel_id];
        $success = $context['embed']['type']['command_success'];
        $failure = $context['embed']['type']['command_error'];
        switch ($arg) {
            case in_array($arg, $events):
                $enable = in_array($option, $starts);
                $disable = in_array($option, $stops);
                if ($enable) {
                    if (!$activeLog) {
                        foreach ($events as $event) {
                            if ($arg !== $event) {
                                $query[$event] = 0;
                            } else {
                                $query[$event] = 1;
                            }
                        }
                        try {
                            DB::table('log_channels')->insert($query);
                            $prefix = $context['prefix'];
                            $success['description'] = "Log channel created! To edit what events are logged use {$prefix}log [event] [enable|disable].
                            Currently only $arg events are enabled in this channel. If you did not mean to create this log channel, run **{$prefix}log disable** to remove it";
                            $message->channel->sendMessage('', false, $success);
                        } catch (\Exception $e) {
                            $failure['description'] = "Error creating log channel, please try again later";
                            $message->channel->sendMessage('', false, $failure);
                        }
                    } else {
                        try {
                            DB::table('log_channels')->where($query)->update([$arg => 1]);
                            $success['description'] = "This channel will now log $arg";
                            $message->channel->sendMessage('', false, $success);
                        } catch (\Exception $e) {
                            $failure['description'] = "Error enabling $arg, please try again later";
                            $message->channel->sendMessage('', false, $failure);
                        }
                    }
                } elseif ($disable) {
                    if (!$activeLog) {
                        $failure['description'] = 'There is not an active log in this channel. Make it before you can break it!';
                        $message->channel->sendMessage('', false, $failure);
                    } else {
                        try {
                            DB::table('log_channels')->where($query)->update([$arg => 0]);
                            $success['description'] = "This channel will no longer log $arg";
                            $message->channel->sendMessage('', false, $success);
                        } catch (\Exception $e) {
                            $failure['description'] = "Error disabling $arg, please try again later";
                            $message->channel->sendMessage('', false, $failure);
                        }
                    }
                } else {
                    $failure['description'] = "$option was an invalid option, please try again with a valid enable|disable option";
                    $message->channel->sendMessage('', false, $failure);
                }
                break;
            case in_array($arg, $starts):
                if ($option) {
                    $failure['description'] = "You have an option in your command, but have chosen the create log channel command. 
                    Remove your option **$option** and try again, or reverse **$arg** with **$option**";
                    $message->channel->sendMessage('', false, $failure);
                    break;
                }
                if($activeLog) {
                    $failure['description'] = 'There is already an active log in this channel. Remove it first before trying to create a new one!';
                    $message->channel->sendMessage('', false, $failure);
                } else {
                    foreach ($events as $event) {
                        $query[$event] = 1;
                    }
                    try {
                        DB::table('log_channels')->insert($query);
                        $prefix = $context['prefix'];
                        $success['description'] = "Log channel created! To edit what events are logged use {$prefix}log [event] [enable|disable] ";
                        $message->channel->sendMessage('', false, $success);
                    } catch (\Exception $e) {
                        $failure['description'] = "Error creating log channel, please try again later";
                        $message->channel->sendMessage('', false, $failure);
                    }
                }

                break;
            case in_array($arg, $stops):
                if ($option) {
                    $failure['description'] = "You have an option in your command, but have chosen the delete log channel command. 
                    Remove your option ($option) and try again, or reverse $arg with $option";
                    $message->channel->sendMessage('', false, $failure);
                    break;
                }
                if(!$activeLog) {
                    $failure['description'] = 'There is not an active log in this channel. Make it before you can break it!';
                    $message->channel->sendMessage('', false, $failure);
                } else {
                    try {
                        DB::table('log_channels')->where($query)->delete();
                        $success['description'] = "Log channel deleted! Time for peace and quiet!";
                        $message->channel->sendMessage('', false, $success);
                    } catch (\Exception $e) {
                        $failure['description'] = "Error deleting log channel, please try again later";
                        $message->channel->sendMessage('', false, $failure);
                    }
                }
                break;
            default:
                dump($arg);
                break;

        }
    }

}