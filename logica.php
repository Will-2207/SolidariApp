<?php
namespace SolidariApp;

require_once __DIR__ . '/vendor/autoload.php';

function generarUUID(): string {
    return bin2hex(random_bytes(16));
}

function procesarDonacion(array $datos, int $usuarioId): array {
    $nombre = trim(strip_tags($datos['nombre_contacto'] ?? ''));
    $email = filter_var(trim($datos['email_contacto'] ?? ''), FILTER_VALIDATE_EMAIL);
    $monto = (float)($datos['monto'] ?? 0);
    $token = generarUUID();

    if (!$nombre || !$email || $monto <= 0) {
        return ['tipo' => 'error', 'mensaje' => 'Datos inválidos.'];
    }

    $estado = ['pg' => false, 'mg' => false];

    // 1. PostgreSQL (Persistencia principal)
    try {
        $db = \SolidariApp\Database::getConnection();
        $stmt = $db->prepare("INSERT INTO donaciones (nombre_contacto, email_contacto, monto, token_uuid, usuario_id) VALUES (?, ?, ?, ?, ?)");
        $estado['pg'] = $stmt->execute([$nombre, $email, $monto, $token, $usuarioId]);
    } catch (\Exception $e) {
        $estado['error_pg'] = $e->getMessage();
    }

    // 2. MongoDB (Respaldo exigido por el taller - Conexión Nativa)
    try {
        // Asegúrate de definir MONGO_URI y MONGO_DB en las variables de entorno de Render
        $manager = new \MongoDB\Driver\Manager(getenv('MONGO_URI'));
        $bulk = new \MongoDB\Driver\BulkWrite;
        $bulk->insert([
            'token_uuid' => $token,
            'usuario_id' => $usuarioId,
            'nombre_contacto' => $nombre,
            'monto' => $monto,
            'email' => $email,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $manager->executeBulkWrite(getenv('MONGO_DB') . '.donaciones', $bulk);
        $estado['mg'] = true;
    } catch (\Exception $e) {
        $estado['error_mg'] = $e->getMessage();
    }

    // Retorno para la interfaz
    if ($estado['pg'] && $estado['mg']) {
        return ['tipo' => 'success', 'mensaje' => 'Donación registrada en PostgreSQL y respaldada en MongoDB.', 'token' => $token];
    } else {
        return ['tipo' => 'warning', 'mensaje' => 'Registro parcial. PG: ' . ($estado['pg'] ? 'OK' : 'FAIL') . ' | Mongo: ' . ($estado['mg'] ? 'OK' : 'FAIL')];
    }
}
