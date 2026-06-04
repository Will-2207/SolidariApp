<?php
namespace SolidariApp;

require_once __DIR__ . '/vendor/autoload.php';

function generarUUID(): string {
    return bin2hex(random_bytes(16));
}

function procesarDonacion(array $datos, int $usuarioId): array {
    // Captura de datos básicos y nuevos campos para el flujo diferenciado
    $tipoAyuda = $datos['tipo_ayuda'] ?? 'monetaria'; // 'fisica' o 'monetaria'
    $campana   = $datos['campana_social'] ?? 'General';
    $nombre    = trim(strip_tags($datos['nombre_contacto'] ?? ''));
    $email     = filter_var(trim($datos['email_contacto'] ?? ''), FILTER_VALIDATE_EMAIL);
    $monto     = (float)($datos['monto'] ?? 0);
    $token     = generarUUID();

    if (!$nombre || !$email) {
        return ['tipo' => 'error', 'mensaje' => 'Datos de contacto inválidos.'];
    }

    $estado = ['pg' => false, 'mg' => false];

    // 1. PostgreSQL (Refactorizado para incluir tipo y campaña)
    try {
        $db = \SolidariApp\Database::getConnection();
        $sql = "INSERT INTO donaciones (nombre_contacto, email_contacto, monto, token_uuid, usuario_id, tipo_ayuda, campana) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $estado['pg'] = $stmt->execute([$nombre, $email, $monto, $token, $usuarioId, $tipoAyuda, $campana]);
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
                'token_uuid'      => $token,
                'usuario_id'      => $usuarioId,
                'nombre_contacto' => $nombre,
                'email'           => $email,
                'monto'           => $monto,
                'tipo_ayuda'      => $tipoAyuda,
                'campana'         => $campana,
                'created_at'      => date('Y-m-d H:i:s')
            ]);
            $manager->executeBulkWrite($dbName . '.donaciones', $bulk);
            $estado['mg'] = true;
        }
    } catch (\Exception $e) {
        $estado['error_mg'] = $e->getMessage();
    }

    // Respuesta final
    if ($estado['pg'] && $estado['mg']) {
        $msg = ($tipoAyuda === 'monetaria') ? 'Donación monetaria registrada. ¡Ayudaste a cambiar vidas! ❤️' : 'Solicitud física registrada. Gracias por tu aporte.';
        return ['tipo' => 'success', 'mensaje' => $msg, 'token' => $token];
    } else {
        return ['tipo' => 'warning', 'mensaje' => 'Registro parcial. PG: ' . ($estado['pg'] ? 'OK' : 'FAIL') . ' | Mongo: ' . ($estado['mg'] ? 'OK' : 'FAIL')];
    }
}
