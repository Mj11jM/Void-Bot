<?php


namespace VoidBot\Events;


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
        $discord->on("GUILD_CREATE", function () {
            //todo
        });
        $discord->on("GUILD_UPDATE", function () {
            //todo
        });
        $discord->on("GUILD_DELETE", function () {
            //todo
        });

        //Guild Member Events
        $discord->on("GUILD_MEMBER_ADD", function () {
            //todo
        });
        $discord->on("GUILD_MEMBER_UPDATE", function () {
            //todo
        });
        $discord->on("GUILD_MEMBER_REMOVE", function () {
            //todo
        });

        //Guild Ban Updates
        $discord->on("GUILD_BAN_ADD", function () {
            //todo
        });
        $discord->on("GUILD_BAN_REMOVE", function () {
            //todo
        });

        //Invite Updates
        $discord->on("INVITE_CREATE", function () {
            //todo
        });
        $discord->on("INVITE_DELETE", function () {
            //todo
        });

        //Some integration thing, idk. Might use it later, might not
        $discord->on("GUILD_INTEGRATIONS_UPDATE", function () {
            //todo
        });
    }

}