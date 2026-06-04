<?php
namespace SolidariApp;
use PDO;

class Database {
    public static function getConnection(): PDO {
        $url = getenv('DATABASE_URL');
        $db = parse_url($url);

        // Si falla el parseo, usamos valores por defecto o lanzamos error
        $host = $db["host"] ?? 'dpg-d8f395t53gjs739kqpk0-a.virginia-postgres.render.com';
        $port = $db["port"] ?? 5432;
        $dbname = ltrim($db["path"] ?? '/solidaria', "/");
        $user = $db["user"] ?? 'solidaria_user';
        $pass = $db["pass"] ?? 'EkxCiKUioNKGCe6CgexD0mAxU5bpSQyC';

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
}
