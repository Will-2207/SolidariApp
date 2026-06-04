<?php
namespace SolidariApp;

require_once __DIR__ . '/vendor/autoload.php';

function generarUUID(): string {
    return bin2hex(random_bytes(16));
}

function procesarDonacion(array $datos, int $usuarioId): array {
    // Captura de datos básicos
    $nombre   = trim(strip_tags($datos['nombre_contacto'] ?? ''));
    $email    = filter_var(trim($datos['email_contacto'] ?? ''), FILTER_VALIDATE_EMAIL);
    $monto    = (float)($datos['monto'] ?? 0);
    $token    = generarUUID();
    
    // Valores por defecto para campos extendidos (evitamos error si la tabla no los tiene)
    $tipoAyuda = $datos['tipo_ayuda'] ?? 'monetaria';
    $campana   = $datos['campana_social'] ?? 'General';

    if (!$nombre || !$email || $monto <= 0) {
        return ['tipo' => 'error', 'mensaje' => 'Datos de contacto o monto inválidos.'];
    }

    $estado = ['pg' => false, 'mg' => false];

    // 1. PostgreSQL - Inserción robusta
    try {
        $db = \SolidariApp\Database::getConnection();
        
        $sql = "INSERT INTO donaciones (nombre_contacto, email_contacto, monto, token_uuid, usuario_id) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $estado['pg'] = $stmt->execute([$nombre, $email, $monto, $token, $usuarioId]);
        
    } catch (\Exception $e) {
        $estado['error_pg'] = $e->getMessage();
    }

    // 2. MongoDB - Independiente de PostgreSQL
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
        } else {
            $estado['error_mg'] = "Configuración MongoDB faltante";
        }
    } catch (\Exception $e) {
        $estado['error_mg'] = $e->getMessage();
    }

    // Log para depuración
    error_log("Resultado PG: " . ($estado['pg'] ? '1' : '0'));
    error_log("Resultado MG: " . ($estado['mg'] ? '1' : '0'));

    // Construcción de mensaje detallado para ambas bases
    $mensajePG = $estado['pg'] ? "Exitoso en PostgreSQL" : "Fallo en PostgreSQL (" . ($estado['error_pg'] ?? 'Error') . ")";
    $mensajeMG = $estado['mg'] ? "Exitoso en MongoDB" : "Fallo en MongoDB (" . ($estado['error_mg'] ?? 'Error') . ")";

    if ($estado['pg'] && $estado['mg']) {
        return [
            'tipo' => 'success', 
            'mensaje' => "¡Donación realizada! $mensajePG y $mensajeMG. ❤️"
        ];
    } else {
        return [
            'tipo' => 'warning', 
            'mensaje' => "Atención: $mensajePG | $mensajeMG"
        ];
    }
}
