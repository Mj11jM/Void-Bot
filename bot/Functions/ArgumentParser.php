<?php


namespace VoidBot\Functions;


class ArgumentParser
{
    private static $instance = null;

    public static function getInstance() {
        if(!self::$instance)
        {
            self::$instance = new ArgumentParser();
        }

        return self::$instance;
    }

    public function parser ($argument) {
        if (empty($argument)) {
            return $argument;
        }
        $newArgs = [];
        foreach ($argument as $arg) {
            switch ($arg) {
                case strpos($arg, "<@") === 0:
                    if (strpos($arg, "<@&") === 0) {
                        $newArgs['role_mentions'][] = $arg;
                    }else {
                        $newArgs['ids'][] = $arg;
                    }
                    break;
                default:
                    $newArgs['args'][] = $arg;
            }
        }
        return $newArgs;
    }

}