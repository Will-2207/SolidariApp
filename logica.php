<?php
/**
 * logica.php - Versión Completa y Corregida
 */

require_once __DIR__ . '/vendor/autoload.php';

// 1. CONFIGURACIÓN
define('MONGO_URI', getenv('MONGO_URI') ?: 'mongodb+srv://usuario_db_cartuji7:ClaveRedSolidaria2026@cluster7.30bhnqx.mongodb.net/?retryWrites=true&w=majority');
define('MONGO_DB', getenv('MONGO_DB') ?: 'prueba1');
define('COL_DONACIONES', 'donaciones');
define('COL_SOPORTE', 'tickets_soporte');

// 2. CONEXIÓN SEGURA
function getMongoClient(): MongoDB\Client {
    return new MongoDB\Client(MONGO_URI, [
        'tls' => true,
        'tlsAllowInvalidCertificates' => false
    ], [
        'typeMap' => ['array' => 'array', 'document' => 'array', 'root' => 'array'],
    ]);
}

// 3. HELPERS
function generarUUID(): string {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function campo(array $data, string $key): string {
    return trim(strip_tags($data[$key] ?? ''));
}

// 4. PROCESAR DONACIONES
function procesarDonacion(array $datos): array {
    try {
        $nombre = campo($datos, 'nombre');
        $tipo = campo($datos, 'tipo_ayuda');
        $desc = campo($datos, 'descripcion');
        
        if (empty($nombre) || empty($tipo) || empty($desc)) {
            return ['tipo' => 'error', 'mensaje' => 'Campos obligatorios incompletos.'];
        }

        $client = getMongoClient();
        $collection = $client->selectCollection(MONGO_DB, COL_DONACIONES);
        $collection->insertOne([
            'nombre' => $nombre,
            'tipo_ayuda' => $tipo,
            'descripcion' => $desc,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);
        
        return ['tipo' => 'success', 'mensaje' => 'Donación registrada exitosamente.'];
    } catch (Exception $e) {
        return ['tipo' => 'error', 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

// 5. PROCESAR SOPORTE (EL QUE ESTABA FALLANDO)
function procesarSoporte(array $datos): array {
    $nombre = campo($datos, 'nombre_contacto');
    $email = filter_var(trim($datos['email_contacto'] ?? ''), FILTER_VALIDATE_EMAIL);
    $categoria = campo($datos, 'categoria_fallo');
    $titulo = campo($datos, 'titulo_falla');
    $descripcion = campo($datos, 'descripcion_falla');

    if (!$nombre || !$email || !$categoria || !$titulo || !$descripcion) {
        return ['tipo' => 'error', 'mensaje' => 'Error: Todos los campos (nombre, email, categoría, título, descripción) son obligatorios.', 'token' => null];
    }

    try {
        $client = getMongoClient();
        $collection = $client->selectCollection(MONGO_DB, COL_SOPORTE);
        $token = generarUUID();

        $collection->insertOne([
            'token_uuid' => $token,
            'nombre_contacto' => $nombre,
            'email_contacto' => $email,
            'categoria_fallo' => $categoria,
            'titulo_falla' => $titulo,
            'descripcion_falla' => $descripcion,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'estado' => 'abierto'
        ]);

        return ['tipo' => 'success', 'mensaje' => 'Reporte enviado con éxito. Token: ' . $token, 'token' => $token];
    } catch (Exception $e) {
        return ['tipo' => 'error', 'mensaje' => 'Error de conexión a MongoDB: ' . $e->getMessage(), 'token' => null];
    }
}
