<?php


namespace VoidBot;

require_once 'vendor/autoload.php';

use MongoDB;

class MongoInstance
{
    private static $instance = null;
    private $connection;

    private function __construct() {
        try
        {
            $configFile = file_get_contents('config.json');
            $config = json_decode($configFile, true);
            $this->connection = new MongoDB\Client($config['mongoDB_address']);
        }
        catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e)
        {
            dump($e);
        }
    }

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new MongoInstance();
        }

        return self::$instance;
    }

    public function getDB() {
        return $this->connection;
    }

}

/* How to use this to access the DB
 * $instance = MongoInstance::getInstance();
 * $prefixDB = $instance->getDB()->voidbot->guildPrefixes;
 * $items = $prefixDB->find()->toArray();
 */