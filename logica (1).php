<?php
/**
 * logica.php — Capa de lógica de negocio
 * Maneja donaciones y tickets de soporte técnico.
 * Conecta con MongoDB Atlas usando la librería oficial de PHP.
 *
 * Instalar dependencia (en Docker/Composer):
 *   composer require mongodb/mongodb
 * Instalar extensión PHP:
 *   pecl install mongodb
 *   echo "extension=mongodb.so" >> /usr/local/etc/php/conf.d/mongodb.ini
 */

require_once __DIR__ . '/vendor/autoload.php';

// ──────────────────────────────────────────────
//  CONFIGURACIÓN DE CONEXIÓN (MongoDB Atlas)
// ──────────────────────────────────────────────
define('MONGO_URI',  getenv('MONGO_URI')  ?: 'mongodb+srv://<usuario>:<password>@<cluster>.mongodb.net/?retryWrites=true&w=majority');
define('MONGO_DB',   getenv('MONGO_DB')   ?: 'solidariapp');
define('COL_DONACIONES', 'donaciones');
define('COL_SOPORTE',    'tickets_soporte');

/**
 * Retorna una instancia del cliente MongoDB.
 * Lanza una excepción si no se puede conectar.
 */
function getMongoClient(): MongoDB\Client
{
    return new MongoDB\Client(MONGO_URI, [], [
        'typeMap' => [
            'array'    => 'array',
            'document' => 'array',
            'root'     => 'array',
        ],
    ]);
}

// ──────────────────────────────────────────────
//  HELPERS
// ──────────────────────────────────────────────

/**
 * Genera un token UUID v4.
 */
function generarUUID(): string
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Sanitiza y retorna un campo de texto, o null si está vacío.
 */
function campo(array $data, string $key): string
{
    return trim(strip_tags($data[$key] ?? ''));
}

/**
 * Simula el envío de un correo (reemplazar con SMTP real en producción).
 * Retorna true si el "envío" fue exitoso.
 */
function simularEnvioEmail(string $destinatario, string $asunto, string $cuerpo): bool
{
    // En producción usar: mail() / PHPMailer / SendGrid / AWS SES
    // error_log("EMAIL → {$destinatario} | {$asunto}");
    return true;
}

// ──────────────────────────────────────────────
//  PROCESAMIENTO DE DONACIONES
// ──────────────────────────────────────────────

/**
 * Valida e inserta una donación en MongoDB.
 *
 * @param  array $datos  Datos del formulario ($_POST)
 * @return array         ['tipo' => 'success'|'error', 'mensaje' => string]
 */
function procesarDonacion(array $datos): array
{
    // ── Validación ──
    $nombre     = campo($datos, 'nombre');
    $tipo_ayuda = campo($datos, 'tipo_ayuda');
    $descripcion= campo($datos, 'descripcion');
    $monto      = floatval($datos['monto'] ?? 0);
    $fundacion  = campo($datos, 'fundacion');

    if (empty($nombre)) {
        return ['tipo' => 'error', 'mensaje' => 'El nombre del donante es obligatorio.'];
    }
    if (strlen($nombre) < 3 || strlen($nombre) > 100) {
        return ['tipo' => 'error', 'mensaje' => 'El nombre debe tener entre 3 y 100 caracteres.'];
    }
    if (empty($tipo_ayuda)) {
        return ['tipo' => 'error', 'mensaje' => 'Debes seleccionar el tipo de ayuda.'];
    }
    if (empty($descripcion)) {
        return ['tipo' => 'error', 'mensaje' => 'La descripción del aporte es obligatoria.'];
    }
    if (strlen($descripcion) < 10) {
        return ['tipo' => 'error', 'mensaje' => 'La descripción debe tener al menos 10 caracteres.'];
    }
    if ($tipo_ayuda === 'monetaria' && $monto <= 0) {
        return ['tipo' => 'error', 'mensaje' => 'Para donaciones monetarias el monto debe ser mayor a cero.'];
    }

    // ── Inserción en MongoDB ──
    try {
        $client     = getMongoClient();
        $collection = $client->selectCollection(MONGO_DB, COL_DONACIONES);

        $documento = [
            'nombre'         => $nombre,
            'tipo_ayuda'     => $tipo_ayuda,
            'monto'          => $tipo_ayuda === 'monetaria' ? $monto : null,
            'descripcion'    => $descripcion,
            'fundacion'      => $fundacion ?: null,
            'estado'         => 'publicada',
            'created_at'     => new MongoDB\BSON\UTCDateTime(),
            'updated_at'     => new MongoDB\BSON\UTCDateTime(),
            'ip_origen'      => $_SERVER['REMOTE_ADDR'] ?? null,
        ];

        $resultado = $collection->insertOne($documento);

        if ($resultado->getInsertedCount() < 1) {
            throw new \RuntimeException('No se insertó ningún documento.');
        }

        return [
            'tipo'    => 'success',
            'mensaje' => "Tu donación de tipo «{$tipo_ayuda}» ha sido registrada exitosamente. "
                       . "ID de referencia: " . $resultado->getInsertedId(),
        ];

    } catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
        error_log('[SolidariApp] MongoDB timeout: ' . $e->getMessage());
        return ['tipo' => 'error', 'mensaje' => 'No se pudo conectar con la base de datos. Inténtalo más tarde.'];
    } catch (Exception $e) {
        error_log('[SolidariApp] Error donación: ' . $e->getMessage());
        return ['tipo' => 'error', 'mensaje' => 'Ocurrió un error interno. Por favor intenta de nuevo.'];
    }
}

// ──────────────────────────────────────────────
//  PROCESAMIENTO DE TICKETS DE SOPORTE
// ──────────────────────────────────────────────

/**
 * Valida e inserta un ticket de soporte en MongoDB.
 * Genera un token UUID único y simula notificación por correo.
 *
 * @param  array $datos  Datos del formulario ($_POST)
 * @return array         ['tipo'=>string, 'mensaje'=>string, 'token'=>string|null]
 */
function procesarSoporte(array $datos): array
{
    // ── Validación ──
    $nombre      = campo($datos, 'nombre_contacto');
    $email       = filter_var(trim($datos['email_contacto'] ?? ''), FILTER_VALIDATE_EMAIL);
    $telefono    = campo($datos, 'telefono_contacto');
    $rol         = campo($datos, 'rol_usuario');
    $categoria   = campo($datos, 'categoria_fallo');
    $prioridad   = campo($datos, 'prioridad');
    $titulo      = campo($datos, 'titulo_falla');
    $descripcion = campo($datos, 'descripcion_falla');
    $pasos       = campo($datos, 'pasos_reproduccion');

    if (empty($nombre)) {
        return ['tipo' => 'error', 'mensaje' => 'El nombre de contacto es obligatorio.', 'token' => null];
    }
    if (!$email) {
        return ['tipo' => 'error', 'mensaje' => 'El correo electrónico no es válido.', 'token' => null];
    }
    if (empty($categoria)) {
        return ['tipo' => 'error', 'mensaje' => 'Debes seleccionar la categoría de la falla.', 'token' => null];
    }
    if (empty($titulo)) {
        return ['tipo' => 'error', 'mensaje' => 'El título del problema es obligatorio.', 'token' => null];
    }
    if (empty($descripcion) || strlen($descripcion) < 20) {
        return ['tipo' => 'error', 'mensaje' => 'La descripción debe tener al menos 20 caracteres.', 'token' => null];
    }

    // Prioridades válidas
    $prioridades_validas = ['baja', 'media', 'alta', 'critica'];
    if (!in_array($prioridad, $prioridades_validas, true)) {
        $prioridad = 'media';
    }

    // ── Generación de token ──
    $token = generarUUID();

    // ── Inserción en MongoDB ──
    try {
        $client     = getMongoClient();
        $collection = $client->selectCollection(MONGO_DB, COL_SOPORTE);

        $documento = [
            'token_uuid'          => $token,
            'nombre_contacto'     => $nombre,
            'email_contacto'      => $email,
            'telefono_contacto'   => $telefono ?: null,
            'rol_usuario'         => $rol ?: null,
            'categoria_fallo'     => $categoria,
            'prioridad'           => $prioridad,
            'titulo_falla'        => $titulo,
            'descripcion_falla'   => $descripcion,
            'pasos_reproduccion'  => $pasos ?: null,
            'estado'              => 'abierto',    // abierto | en_revision | resuelto | cerrado
            'respuesta_admin'     => null,
            'fecha_resolucion'    => null,
            'created_at'          => new MongoDB\BSON\UTCDateTime(),
            'updated_at'          => new MongoDB\BSON\UTCDateTime(),
            'ip_origen'           => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent'          => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ];

        $resultado = $collection->insertOne($documento);

        if ($resultado->getInsertedCount() < 1) {
            throw new \RuntimeException('No se insertó el ticket.');
        }

        // ── Notificación por correo (simulada) ──
        $asunto_email = "SolidariApp — Tu ticket de soporte #{$token}";
        $cuerpo_email = "Hola {$nombre},\n\n"
            . "Hemos recibido tu reporte de incidencia.\n\n"
            . "Token de seguimiento: {$token}\n"
            . "Categoría: {$categoria}\n"
            . "Prioridad: {$prioridad}\n"
            . "Título: {$titulo}\n\n"
            . "Recibirás una respuesta en máximo 4 horas hábiles.\n\n"
            . "Equipo SolidariApp";

        simularEnvioEmail($email, $asunto_email, $cuerpo_email);

        return [
            'tipo'    => 'success',
            'mensaje' => "Tu reporte «{$titulo}» fue registrado. "
                       . "Enviaremos el token a {$email}.",
            'token'   => $token,
        ];

    } catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
        error_log('[SolidariApp] MongoDB timeout: ' . $e->getMessage());
        return ['tipo' => 'error', 'mensaje' => 'No se pudo conectar con la base de datos. Inténtalo más tarde.', 'token' => null];
    } catch (Exception $e) {
        error_log('[SolidariApp] Error soporte: ' . $e->getMessage());
        return ['tipo' => 'error', 'mensaje' => 'Error interno al registrar el ticket. Inténtalo de nuevo.', 'token' => null];
    }
}
