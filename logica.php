<?php
namespace SolidariApp;

require_once __DIR__ . '/vendor/autoload.php';

// ... (mantén tus constantes de MONGO_URI, etc.) ...

function procesarSoporte(array $datos): array {
    $nombre = campo($datos, 'nombre_contacto');
    $email = filter_var(trim($datos['email_contacto'] ?? ''), FILTER_VALIDATE_EMAIL);
    $categoria = campo($datos, 'categoria_fallo');
    $titulo = campo($datos, 'titulo_falla');
    $descripcion = campo($datos, 'descripcion_falla');
    $token = generarUUID();

    if (!$nombre || !$email || !$categoria || !$titulo || !$descripcion) {
        return ['tipo' => 'error', 'mensaje' => 'Campos obligatorios incompletos.', 'token' => null];
    }

    // 1. Persistencia en MongoDB
    try {
        $client = getMongoClient();
        $collection = $client->selectCollection(MONGO_DB, COL_SOPORTE);
        $collection->insertOne([
            'token_uuid' => $token, 'nombre_contacto' => $nombre, 'email_contacto' => $email,
            'categoria_fallo' => $categoria, 'titulo_falla' => $titulo,
            'descripcion_falla' => $descripcion, 'created_at' => new \MongoDB\BSON\UTCDateTime(),
            'estado' => 'abierto'
        ]);
    } catch (\Exception $e) {
        return ['tipo' => 'error', 'mensaje' => 'Error en sistema: ' . $e->getMessage(), 'token' => null];
    }

    // 2. Persistencia en PostgreSQL (usando la tabla correcta: tickets_soporte)
    try {
        $db = \SolidariApp\Database::getConnection();
        $db->beginTransaction(); // Iniciamos transacción profesional

        $sql = "INSERT INTO tickets_soporte 
                (nombre_contacto, email_contacto, categoria_fallo, titulo_falla, descripcion_falla, token_uuid) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$nombre, $email, $categoria, $titulo, $descripcion, $token]);
        
        $db->commit(); // Todo salió bien
    } catch (\Exception $e) {
        if (isset($db)) $db->rollBack(); // Si falla Postgres, revertimos
        error_log("Error en persistencia SQL: " . $e->getMessage());
    }

    return ['tipo' => 'success', 'mensaje' => 'Ticket de soporte registrado correctamente.', 'token' => $token];
}
