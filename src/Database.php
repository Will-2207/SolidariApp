<?php
namespace SolidariApp;
use PDO;

class Database {
    public static function getConnection(): PDO {
        // Leemos la URL completa desde la variable de entorno
        $url = getenv('DATABASE_URL');
        $db = parse_url($url);

        $host = $db["host"];
        $port = $db["port"];
        $dbname = ltrim($db["path"], "/");
        $user = $db["user"];
        $pass = $db["pass"];

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
}
