<?php


namespace VoidBot\Events;

use Carbon\Carbon;
use Discord\Discord;
use Discord\Parts\Guild\Ban;
use Discord\Parts\Guild\Guild;
use Discord\Parts\Guild\Invite;
use Discord\Parts\Guild\Role;
use Discord\Parts\User\Member;
use Illuminate\Database\Capsule\Manager as DB;
use VoidBot\Functions\ContextCreator;
use VoidBot\Functions\LogFinder;

class GuildEvents
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new GuildEvents();
        }

        return self::$instance;
    }

    public function events($discord): void{

        //Proper Guild Events
        $discord->on("GUILD_CREATE", function (Guild $guild, Discord $discord) {
            $oldGuild = DB::table('guilds')->where('guild_id', '=', $guild->id)->first();
            if (!$oldGuild) {
                DB::table('guilds')->insert(
                    [
                        'guild_id' => $guild->id,
                        'guild_name' => $guild->name,
                        'prefix' => '-'
                    ]);
            }
        });

        $discord->on("GUILD_UPDATE", function (Guild $new, Discord $discord, Guild $old) {
            //todo
        });

        $discord->on("GUILD_DELETE", function (Guild $guild, Discord $discord, bool $unavailable) {
            if ($unavailable){
                return;
            }
            try {
                DB::table('guilds')->where('guild_id', '=', $guild->id)->delete();
                dump("Deleted $guild->name from database");
            } catch (\Exception $e) {
                dump("Unable to delete $guild->id from database.");
                dump($e);
            }
        });

        //Guild Member Events
        $discord->on("GUILD_MEMBER_ADD", function (Member $member, Discord $discord) {
            $activeLog = LogFinder::findEventLog($member->guild_id, ['member_add']);
            IF (!$activeLog) {
                return;
            }
            $context = ContextCreator::getInstance()->contextCreation(null, $discord, true);
            foreach ($activeLog as $logChannel) {
                $embed = $context['embed']['type']['command_success'];
                $embed['author']['name'] = "New Member $member->username!";
                $embed['author']['icon_url'] = $member->user->avatar;

                $createdDate = new Carbon($member->user->createdTimestamp());
                $embed['fields'] = [
                    0 => [
                        'name' => "Account Created",
                        'value' => $createdDate->isoFormat('MMMM Do YYYY, h:mm:ss a'),
                        'inline' => true
                    ],
                    1 => [
                        'name' => "Mention",
                        'value' => "<@$member->id>",
                        'inline' => true
                    ],
                    2 => [
                    'name' => "User ID",
                    'value' => "$member->id",
                    'inline' => false
                    ]
                ];
                $discord->getChannel($logChannel->channel_id)->sendMessage('', false, $embed);
            }
        });

        $discord->on("GUILD_MEMBER_UPDATE", function (Member $new, Discord $discord, Member $old) {


            $oldRole = $old->roles->toArray();
            $newRole = $new->roles->toArray();
            $newRoles = empty(array_keys(array_diff($newRole, $oldRole))[0])? null : array_keys(array_diff($newRole, $oldRole));
            $removedRoles = empty(array_keys(array_diff($oldRole, $newRole))[0])? null : array_keys(array_diff($oldRole, $newRole));

            $added = !is_null($newRoles);
            $removed = !is_null($removedRoles);

            $avatar_change = $old->user->avatar !== $new->user->avatar;

            $username_change = $old->username !== $new->username;


            $nickname_change = $old->nick !== $new->nick;
            $oldNick = !is_null($old->nick)? $old->nick: $old->username;
            $newNick = !is_null($new->nick)? $new->nick: $new->username;

            $discriminator_change = $old->discriminator !== $new->discriminator;


            $query = [];
            $query[] = 'member_update';
            $added? $query[] = 'role_add': null;
            $removed? $query[] = 'role_removed': null;
            $avatar_change? $query[] = 'avatar_change': null;
            $username_change? $query[] = 'name_change': null;
            $nickname_change? $query[] = 'nickname_change': null;
            $discriminator_change? $query[] = 'discriminator_change': null;

            if (count($query) === 1) {
                return;
            }
            $activeLog = LogFinder::findEventLog($new->guild_id, $query);
            IF (!$activeLog) {
                return;
            }
            $context = ContextCreator::getInstance()->contextCreation(null, $discord, true);
            $embed = $context['embed']['type']['command_success'];
            $embed['fields'] = [];
            if ($avatar_change) {
                $embed['author']['name'] = "Avatar Updated: $new->username";
                $embed['thumbnail']['url'] = $new->user->avatar;
                if (!is_null($old->user->avatar)) {
                    $embed['author']['icon_url'] = $old->user->avatar;
                }
            } else {
                $embed['author'] = [
                    'name' => "Member Updated: $new->username",
                    'icon_url' => $new->user->avatar
                    ];
            }
            $roleList = "";
            if ($added || $removed) {
                if ($added) {
                    foreach ($newRoles as $role) {
                        $roleList .= "<@&$role> ";
                    }
                } else {
                    foreach ($removedRoles as $role) {
                        $roleList .= "<@&$role> ";
                    }
                }

            }
            $checks = [
                'added' => [
                    $added => [
                        [
                            'name' => 'Role Added',
                            'value' => $roleList
                        ]
                    ]
                ],
                'removed' => [
                    $removed => [
                        [
                            'name' => 'Role Removed',
                            'value' => $roleList
                        ]
                    ]],
                'username' => [
                    $username_change => [
                        [
                            'name' => 'Old Username',
                            'value' => "$old->username"
                        ],
                        [
                            'name' => 'New Username',
                            'value' => "$new->username"
                        ]
                    ]],
                'nickname' => [
                    $nickname_change => [
                        [
                            'name' => 'Old Nickname',
                            'value' => "$oldNick"
                        ],
                        [
                            'name' => 'New Nickname',
                            'value' => "$newNick"
                        ]
                ]],
                'discrim' => [
                    $discriminator_change => [
                        [
                            'name' => 'Old Discriminator',
                            'value' => "$old->discriminator"
                        ],
                        [
                            'name' => 'New Discriminator',
                            'value' => "$new->discriminator"
                        ]
                ]]];
            foreach ($checks as $items) {
                if (!empty($items[1])) {
                    $embed['fields'] = $items[1];
                }
            }
            foreach ($activeLog as $logChannel) {
                $discord->getChannel($logChannel->channel_id)->sendMessage('', false, $embed);
            }
        });

        $discord->on("GUILD_MEMBER_REMOVE", function (Member $member, Discord $discord) {
            $activeLog = LogFinder::findEventLog($member->guild_id, ['member_remove']);
            IF (!$activeLog || $member->id === $discord->id) {
                return;
            }
            $context = ContextCreator::getInstance()->contextCreation(null, $discord, true);
            foreach ($activeLog as $logChannel) {
                $embed = $context['embed']['type']['command_success'];
                $embed['author']['name'] = "User Left $member->username!";
                $embed['author']['icon_url'] = $member->user->avatar;

                $createdDate = new Carbon($member->user->createdTimestamp());
                $joinedAt = new Carbon($member->joined_at);
                $embed['fields'] = [
                    0 => [
                        'name' => "Account Created",
                        'value' => $createdDate->isoFormat('MMMM Do YYYY, h:mm:ss a'),
                        'inline' => true
                    ],
                    1 => [
                        'name' => "Time Since Joined",
                        'value' => $joinedAt->diffForHumans(Carbon::now()),
                        'inline' => true
                    ]
                ];
                dump($embed);
                $discord->getChannel($logChannel->channel_id)->sendMessage('', false, $embed);
            }
        });

        //Guild Ban Updates
        $discord->on("GUILD_BAN_ADD", function (Ban $ban, Discord $discord) {
            $activeLog = LogFinder::findEventLog($ban->guild_id, ['ban_add']);
            IF (!$activeLog) {
                return;
            }
        });

        $discord->on("GUILD_BAN_REMOVE", function ($ban, Discord $discord) {
            $activeLog = LogFinder::findEventLog($ban->guild_id, ['ban_remove']);
            IF (!$activeLog) {
                return;
            }
        });

        //Invite Updates
        $discord->on("INVITE_CREATE", function (Invite $invite, Discord $discord) {
            $activeLog = LogFinder::findEventLog($invite->guild_id, ['invite_create']);
            IF (!$activeLog) {
                return;
            }
        });

        $discord->on("INVITE_DELETE", function ($invite, Discord $discord) {
            $activeLog = LogFinder::findEventLog($invite->guild_id, ['invite_delete']);
            IF (!$activeLog) {
                return;
            }
        });

        //Some integration thing, idk. Might use it later, might not
        $discord->on("GUILD_INTEGRATIONS_UPDATE", function () {
            //todo
        });

        $discord->on("GUILD_ROLE_CREATE", function (Role $role, Discord $discord) {
            $activeLog = LogFinder::findEventLog($role->guild_id, ['role_create']);
            IF (!$activeLog) {
                return;
            }
        });

        $discord->on("GUILD_ROLE_UPDATE", function (Role $role, Discord $discord, $oldRole) {
            $activeLog = LogFinder::findEventLog($role->guild_id, ['role_update']);
            IF (!$activeLog) {
                return;
            }
        });

        $discord->on("GUILD_ROLE_DELETE", function (Role $role, Discord $discord) {
            $activeLog = LogFinder::findEventLog($role->guild_id, ['role_delete']);
            IF (!$activeLog) {
                return;
            }
        });
    }

}