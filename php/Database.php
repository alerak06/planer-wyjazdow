<?php

namespace Triptrav\Core;

use mysqli;

class Database
{
    private static $connection = null;

    public static function connect()
    {
        if (self::$connection === null) {

            require __DIR__ . "/../../config/database.php";

            self::$connection = $conn;

            if (self::$connection->connect_error) {
                die("Błąd połączenia z bazą: " . self::$connection->connect_error);
            }
        }

        return self::$connection;
    }
}