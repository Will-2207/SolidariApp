<?php
namespace SolidariApp;

require_once __DIR__ . '/vendor/autoload.php';

function generarUUID(): string { return bin2hex(random_bytes(16)); }

function procesarDonacion(array $datos, int $usuarioId): array {
    $nombre = trim(strip_tags($datos['nombre_contacto'] ?? ''));
    $email = filter_var(trim($datos['email_contacto'] ?? ''), FILTER_VALIDATE_EMAIL);
    $monto = (float)($datos['monto'] ?? 0);
    $token = generarUUID();

    if (!$nombre || !$email || $monto <= 0) {
        return ['tipo' => 'error', 'mensaje' => 'Datos inválidos.'];
    }

    $status = ['pg' => false, 'mg' => false];

    // 1. PostgreSQL
    try {
        $db = \SolidariApp\Database::getConnection();
        $stmt = $db->prepare("INSERT INTO donaciones (nombre_contacto, email_contacto, monto, token_uuid, usuario_id) VALUES (?, ?, ?, ?, ?)");
        $status['pg'] = $stmt->execute([$nombre, $email, $monto, $token, $usuarioId]);
    } catch (\Exception $e) { $status['error_pg'] = $e->getMessage(); }

    // 2. MongoDB (Nativo, sin Composer, cumple el requisito)
    try {
        $manager = new \MongoDB\Driver\Manager(getenv('MONGO_URI'));
        $bulk = new \MongoDB\Driver\BulkWrite;
        $bulk->insert([
            'token_uuid' => $token,
            'usuario_id' => $usuarioId,
            'nombre' => $nombre,
            'monto' => $monto,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $manager->executeBulkWrite(getenv('MONGO_DB') . '.donaciones', $bulk);
        $status['mg'] = true;
    } catch (\Exception $e) { $status['error_mg'] = $e->getMessage(); }

    return [
        'tipo' => ($status['pg'] && $status['mg']) ? 'success' : 'warning',
        'mensaje' => "PG: " . ($status['pg'] ? "OK" : "Error") . " | Mongo: " . ($status['mg'] ? "OK" : "Error"),
        'token' => $token
    ];
}
