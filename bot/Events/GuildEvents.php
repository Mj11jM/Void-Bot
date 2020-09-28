<?php


namespace VoidBot\Events;


use VoidBot\MongoInstance;

class GuildEvents
{
    private static $instance = null;
    private $mongoDB;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new GuildEvents();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->mongoDB = MongoInstance::getInstance();
    }

    public function events($discord): void{

        //Proper Guild Events
        $discord->on("GUILD_CREATE", function ($guild, $discord) {
            $prefixDB = $this->mongoDB->getDB()->voidbot->guildPrefixes;
            $prefixDB->insertOne(["guild_id" => $guild->id, "prefix" => '-']);
        });
        $discord->on("GUILD_UPDATE", function () {
            //todo
        });
        $discord->on("GUILD_DELETE", function ($guild, $discord) {
            $prefixDB = $this->mongoDB->getDB()->voidbot->guildPrefixes;
            $prefixDB->deleteOne(["guild_id" => $guild->id, "prefix" => '-']);
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