<?php
require_once __DIR__ . '/vendor/autoload.php';

// 1. CONFIGURACIÓN
define('MONGO_URI', getenv('MONGO_URI') ?: 'mongodb+srv://cartuji7_db_user:ClaveRedSolidaria2026@cluster7.30bhnqx.mongodb.net/?retryWrites=true&w=majority&appName=Cluster7');
define('MONGO_DB', getenv('MONGO_DB') ?: 'prueba1');
define('COL_DONACIONES', 'donaciones');
define('COL_SOPORTE', 'tickets_soporte');

// 2. CONEXIÓN (Mantenemos tu lógica original)
function getMongoClient(): MongoDB\Client {
    return new MongoDB\Client(MONGO_URI, ['tls' => true], [
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

// 4. PROCESAR SOPORTE (INTEGRACIÓN DUAL: MONGO + POSTGRES)
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

    // A. GUARDAR EN MONGODB (Tu lógica original intacta)
    try {
        $client = getMongoClient();
        $collection = $client->selectCollection(MONGO_DB, COL_SOPORTE);
        $collection->insertOne([
            'token_uuid' => $token, 'nombre_contacto' => $nombre, 'email_contacto' => $email,
            'categoria_fallo' => $categoria, 'titulo_falla' => $titulo,
            'descripcion_falla' => $descripcion, 'created_at' => new MongoDB\BSON\UTCDateTime(),
            'estado' => 'abierto'
        ]);
    } catch (Exception $e) {
        return ['tipo' => 'error', 'mensaje' => 'Error Mongo: ' . $e->getMessage(), 'token' => null];
    }

    // B. GUARDAR EN POSTGRESQL (Nueva capa añadida)
    try {
        $db = \SolidariApp\Database::getConnection();
        $stmt = $db->prepare("INSERT INTO donaciones (nombre_contacto, email_contacto, categoria_fallo, titulo_falla, descripcion_falla, token_uuid) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $email, $categoria, $titulo, $descripcion, $token]);
    } catch (Exception $e) {
        error_log("Error guardando en Postgres: " . $e->getMessage());
        // No retornamos error aquí para no confundir al usuario si Mongo ya guardó
    }

    return ['tipo' => 'success', 'mensaje' => 'Reporte enviado con éxito.', 'token' => $token];
}
