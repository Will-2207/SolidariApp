<?php
namespace SolidariApp;
use PDO;

class Database {
    public static function getConnection(): PDO {
        // Forzamos los valores específicos para evitar errores de lectura
        $host = 'dpg-d8f395t53gjs739kqpk0-a.virginia-postgres.render.com';
        $port = 5432;
        $dbname = 'solidaria';
        $user = 'solidaria_user';
        $pass = 'EkxCiKUioNKGCe6CgexD0mAxU5bpSQyC';

        // El DSN debe incluir sslmode=require para que Render acepte la conexión
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
        
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
}
