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

    // 1. PostgreSQL
    try {
        $db = \SolidariApp\Database::getConnection();
        $stmt = $db->prepare("INSERT INTO donaciones (nombre_contacto, email_contacto, monto, token_uuid, usuario_id) VALUES (?, ?, ?, ?, ?)");
        $estado['pg'] = $stmt->execute([$nombre, $email, $monto, $token, $usuarioId]);
    } catch (\Exception $e) {
        $estado['error_pg'] = $e->getMessage();
    }

    // 2. MongoDB
    try {
        $uri = getenv('MONGO_URI');
        $dbName = getenv('MONGO_DB');
        if ($uri && $dbName) {
            $manager = new \MongoDB\Driver\Manager($uri);
            $bulk = new \MongoDB\Driver\BulkWrite;
            $bulk->insert([
                'token_uuid' => $token,
                'usuario_id' => $usuarioId,
                'nombre_contacto' => $nombre,
                'email' => $email,
                'monto' => $monto,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $manager->executeBulkWrite($dbName . '.donaciones', $bulk);
            $estado['mg'] = true;
        }
    } catch (\Exception $e) {
        $estado['error_mg'] = $e->getMessage();
    }

    if ($estado['pg'] && $estado['mg']) {
        return ['tipo' => 'success', 'mensaje' => '¡Éxito! Registrado en PG y MongoDB.', 'token' => $token];
    } else {
        return ['tipo' => 'warning', 'mensaje' => 'Registro parcial. PG: ' . ($estado['pg'] ? 'OK' : 'FAIL') . ' | Mongo: ' . ($estado['mg'] ? 'OK' : 'FAIL')];
    }
}
