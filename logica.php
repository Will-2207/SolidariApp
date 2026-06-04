<?php
namespace SolidariApp;

require_once __DIR__ . '/vendor/autoload.php';

function generarUUID(): string {
    return bin2hex(random_bytes(16));
}

function campo(array $data, string $key): string {
    return trim(strip_tags($data[$key] ?? ''));
}

// PROCESAR DONACIÓN: Solo PostgreSQL
function procesarDonacion(array $datos, int $usuarioId): array {
    $nombre = campo($datos, 'nombre_contacto');
    $email = filter_var(trim($datos['email_contacto'] ?? ''), FILTER_VALIDATE_EMAIL);
    $monto = (float)($datos['monto'] ?? 0);
    $token = generarUUID();

    if (!$nombre || !$email || $monto <= 0) {
        return ['tipo' => 'error', 'mensaje' => 'Datos de donación inválidos.'];
    }

    try {
        $db = \SolidariApp\Database::getConnection();
        $sql = "INSERT INTO donaciones (nombre_contacto, email_contacto, monto, token_uuid, usuario_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$nombre, $email, $monto, $token, $usuarioId]);
        
        return ['tipo' => 'success', 'mensaje' => 'Donación registrada exitosamente.', 'token' => $token];
    } catch (\Exception $e) {
        return ['tipo' => 'error', 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}
