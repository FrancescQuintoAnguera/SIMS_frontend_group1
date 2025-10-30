<?php
require 'vendor/autoload.php';

use MongoDB\Client;

class DatabaseMongo {
    private static $client;

    public static function getConnection() {
        if (!self::$client) {
            $mongoUser = getenv('MONGO_INITDB_ROOT_USERNAME');
            $mongoPass = getenv('MONGO_INITDB_ROOT_PASSWORD');
            $mongoHost = getenv('MONGO_HOST') ?: 'mongodb';
            $mongoPort = getenv('MONGO_PORT') ?: '27017';
            $mongoDb = getenv('MONGO_INITDB_DATABASE');

            if (!$mongoUser || !$mongoPass) {
                throw new Exception("MongoDB credentials not configured. Please check your .env file.");
            }

            $connectionString = "mongodb://{$mongoUser}:{$mongoPass}@{$mongoHost}:{$mongoPort}/?authSource=admin";

            self::$client = new Client($connectionString);
        }
        
        $dbName = getenv($mongoDb);
        return self::$client->$mongoDb;
    }
}