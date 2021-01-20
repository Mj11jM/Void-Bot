<?php


namespace VoidBot;

use Illuminate\Database\Capsule\Manager as Capsule;

require_once 'vendor/autoload.php';


class MySQLInstance
{
    private static $instance = null;

    private function __construct() {
        try
        {

            $configFile = file_get_contents('config.json');
            $config = json_decode($configFile, true);
            $capsule = new Capsule;
            $capsule->addConnection($config['database']);
            $capsule->setAsGlobal();
        }
        catch (\Throwable $e)
        {
            dump($e);
        }
    }

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new MySQLInstance();
        }

        return self::$instance;
    }


}