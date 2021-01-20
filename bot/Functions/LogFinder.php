<?php


namespace VoidBot\Functions;

use Illuminate\Database\Capsule\Manager as DB;

class LogFinder
{

    public static function findEventLog($guildID, $event) {
        $query = [];
        $query[] = ['guild_id', '=', $guildID];
        foreach ($event as $ev) {
            $query[] = [$ev, '=', 1];
        }
        $logFound = DB::table('log_channels')->where($query)->get();
        if (!empty($logFound[0])) {
            return $logFound;
        }

        return false;
    }

    public static function findLog($guildID, $channelID) {
        $logFound = DB::table('log_channels')->where([['guild_id', '=', $guildID], ['channel_id', '=', $channelID]])->first();
        if (!empty($logFound)) {
            return $logFound;
        }

        return false;
    }
}