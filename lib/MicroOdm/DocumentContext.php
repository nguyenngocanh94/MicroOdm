<?php
declare(strict_types=1);

namespace MicroOdm;

use MongoDB\Client;

/**
 * endpoint
 * Class DocumentContext
 * @package MicroOdm
 */
class DocumentContext
{
    public static function initialDbConnection(string $host, int $port, string $database, string $user, string $password,
                                               bool $isReplica, string $replicaSetName, array $replicaConfigs = []) : array{
        list($mgDb, $params, $dbName) = self::getMongoConfiguration($host, $port, $database, $user, $password,
            $isReplica, $replicaSetName, $replicaConfigs);
        $mongo = new Client($mgDb, $params);
        $db = $mongo->{$dbName};
        return array($mongo, $db);
    }

    private static function getMongoConfiguration(string $host, int $port, string $database, string $user, string $password,
                                                  bool $isReplica, string $replicaSetName = "rs0", array $replicaConfigs = [])
    {
        $params = [];
        $mgDb = "mongodb://" . $user . ":" . $password;
        if (!$isReplica){
            $mgDb = $mgDb. "@". $host . ":" . $port;
        }else{
            $params = [
                "replicaSet" => $replicaSetName,
                "db" => $database
            ];

            $c = 1;
            foreach ($replicaConfigs as $dbToUse) {
                $mongoHost = isset($dbToUse["host"]) ? $dbToUse["host"] : $port;
                $mongoPort = isset($dbToUse["port"]) ? $dbToUse["port"] : $port;
                $new = $mongoHost . ":" . $mongoPort;
                $mgDb = ($c > 1) ? $mgDb . "," . $new : $mgDb . $new;
                $c++;
            }
        }
        return [$mgDb, $params, $database];
    }
}