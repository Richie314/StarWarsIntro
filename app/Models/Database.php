<?php
declare(strict_types=1);
namespace StarWars\Models;

use mysqli;
use RuntimeException;

final class Database
{
    private static ?mysqli $connection = null;

    public static function connection(): mysqli
    {
        if (self::$connection !== null) {
            return self::$connection;
        }
        foreach (['MYSQL_HOST', 'MYSQL_USER', 'MYSQL_DATABASE_NAME'] as $name) {
            if (!getenv($name)) {
                throw new RuntimeException('Database configuration is missing.');
            }
        }
        self::$connection = new mysqli(
            getenv('MYSQL_HOST'), getenv('MYSQL_USER'),
            getenv('MYSQL_PASSWORD') ?: '', getenv('MYSQL_DATABASE_NAME')
        );
        self::$connection->set_charset('utf8mb4');
        return self::$connection;
    }
}
