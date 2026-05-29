<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
$mensaje = '';
$tipo_alerta = '';
$token_generado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo_form']) && $_POST['tipo_form'] === 'soporte') {
    if (!defined('MONGO_URI')) {
        require_once __DIR__ . '/logica.php';
    }
    $resultado = procesarSoporte($_POST);
    $mensaje = $resultado['mensaje'];
    $tipo_alerta = $resultado['tipo'];
    $token_generado = $resultado['token'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SolidariApp — Soporte Técnico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --navy:      #0A1628;
      --navy-mid:  #112240;
      --navy-soft: #1B3A6B;
      --accent:    #2563EB;
      --accent-lt: #3B82F6;
      --gold:      #F59E0B;
      --surface:   #F0F4FF;
      --muted:     #64748B;
      --border:    #CBD5E1;
      --white:     #FFFFFF;
      --radius:    14px;
      --shadow:    0 4px 24px rgba(10,22,40,.10);
    }
    *, *::before, *::after { box-sizing: border-box; }
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--surface);
      color: var(--navy);
      min-height: 100vh;
      margin: 0;
    }

    /* SIDEBAR — reutilizado del index */
    .sidebar {
      position: fixed; top: 0; left: 0;
      width: 260px; height: 100vh;
      background: var(--navy);
      display: flex; flex-direction: column;
      z-index: 100;
      border-right: 1px solid rgba(255,255,255,.06);
    }
    .sidebar-brand { padding: 28px 24px 20px; border-bottom: 1px solid rgba(255,255,255,.08); }
    .sidebar-brand .logo-mark {
      width: 38px; height: 38px; background: var(--accent); border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; color: #fff; margin-bottom: 10px;
    }
    .sidebar-brand h1 { font-size: 17px; font-weight: 800; color: #fff; margin: 0; }
    .sidebar-brand p  { font-size: 11px; color: rgba(255,255,255,.45); margin: 0; text-transform: uppercase; letter-spacing: .5px; }
    .nav-section { padding: 20px 14px 4px; font-size: 10px; font-weight: 700; color: rgba(255,255,255,.30); text-transform: uppercase; letter-spacing: 1px; }
    .sidebar-nav { flex: 1; padding: 8px 14px; }
    .sidebar-nav .nav-link {
      display: flex; align-items: center; gap: 12px;
      color: rgba(255,255,255,.60); padding: 11px 14px; border-radius: 10px;
      font-size: 14px; font-weight: 500; text-decoration: none;
      transition: all .18s; margin-bottom: 3px;
    }
    .sidebar-nav .nav-link i { font-size: 18px; }
    .sidebar-nav .nav-link:hover { background: rgba(255,255,255,.07); color: #fff; }
    .sidebar-nav .nav-link.active { background: var(--accent); color: #fff; box-shadow: 0 4px 14px rgba(37,99,235,.40); }
    .sidebar-footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.08); }
    .sidebar-footer .version { font-size: 11px; color: rgba(255,255,255,.25); text-align: center; }

    .main-wrapper { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; }
    .topbar {
      background: var(--white); border-bottom: 1px solid var(--border);
      padding: 16px 36px; display: flex; align-items: center; justify-content: space-between;
      position: sticky; top: 0; z-index: 90;
    }
    .topbar-title { font-size: 15px; font-weight: 700; color: var(--navy); }
    .topbar-sub   { font-size: 12px; color: var(--muted); margin-top: 1px; }
    .topbar-right { display: flex; align-items: center; gap: 12px; }
    .avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--accent); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 13px; font-weight: 700; }
    .page-content { padding: 36px; flex: 1; }

    /* FORM CARD */
    .form-card { background: var(--white); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); overflow: hidden; }
    .form-card-header { background: var(--navy); padding: 22px 28px; display: flex; align-items: center; gap: 14px; }
    .form-card-header .hicon { width: 42px; height: 42px; border-radius: 10px; background: rgba(255,255,255,.10); display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; }
    .form-card-header h2 { font-size: 16px; font-weight: 700; color: #fff; margin: 0; }
    .form-card-header p  { font-size: 12px; color: rgba(255,255,255,.50); margin: 2px 0 0; }
    .form-card-body { padding: 28px; }

    /* FORMS */
    .form-label { font-size: 12px; font-weight: 700; color: var(--navy); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 7px; }
    .form-control, .form-select { border: 1.5px solid var(--border); border-radius: 10px; padding: 11px 14px; font-size: 14px; color: var(--navy); font-family: 'Plus Jakarta Sans', sans-serif; transition: border-color .18s, box-shadow .18s; }
    .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
    .form-control::placeholder { color: var(--muted); }
    textarea.form-control { resize: none; }
    .input-prefix { background: var(--surface); border: 1.5px solid var(--border); border-right: none; border-radius: 10px 0 0 10px; padding: 11px 14px; font-size: 14px; font-weight: 600; color: var(--muted); }
    .input-prefix + .form-control { border-radius: 0 10px 10px 0; }

    .btn-submit { background: var(--accent); color: #fff; border: none; border-radius: 10px; padding: 13px 28px; font-size: 14px; font-weight: 700; font-family: 'Plus Jakarta Sans', sans-serif; cursor: pointer; transition: background .18s, transform .12s, box-shadow .18s; box-shadow: 0 4px 14px rgba(37,99,235,.35); }
    .btn-submit:hover { background: #1D4ED8; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,.45); }

    /* CATEGORY CARDS */
    .cat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 4px; }
    .cat-option { display: none; }
    .cat-label {
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      gap: 8px; padding: 14px 8px; border-radius: 12px;
      border: 1.5px solid var(--border); background: var(--surface);
      cursor: pointer; transition: all .18s;
      font-size: 12px; font-weight: 600; color: var(--muted);
      text-align: center; line-height: 1.3;
    }
    .cat-label i { font-size: 22px; }
    .cat-option:checked + .cat-label {
      border-color: var(--accent); background: #EFF6FF;
      color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,.12);
    }
    .cat-label:hover { border-color: var(--accent-lt); color: var(--accent-lt); background: #F8FAFF; }

    /* PRIORITY BADGES */
    .priority-row { display: flex; gap: 8px; flex-wrap: wrap; }
    .priority-option { display: none; }
    .priority-label {
      padding: 8px 16px; border-radius: 8px;
      border: 1.5px solid var(--border); background: var(--surface);
      font-size: 13px; font-weight: 600; cursor: pointer;
      transition: all .15s; color: var(--muted);
    }
    .priority-option:checked + .priority-label { border-color: currentColor; }
    .priority-option[value="baja"]:checked   + .priority-label { color: #059669; background: #ECFDF5; border-color: #059669; }
    .priority-option[value="media"]:checked  + .priority-label { color: #D97706; background: #FFFBEB; border-color: #D97706; }
    .priority-option[value="alta"]:checked   + .priority-label { color: #DC2626; background: #FEF2F2; border-color: #DC2626; }
    .priority-option[value="critica"]:checked + .priority-label { color: #7C3AED; background: #F5F3FF; border-color: #7C3AED; }

    /* ALERT */
    .alert-modern { border-radius: 12px; border: none; padding: 20px 24px; display: flex; align-items: flex-start; gap: 14px; font-size: 14px; font-weight: 500; }
    .alert-modern .alert-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .alert-modern.success { background: #ECFDF5; color: #065F46; }
    .alert-modern.success .alert-icon { background: #D1FAE5; color: #059669; }
    .alert-modern.error   { background: #FEF2F2; color: #991B1B; }
    .alert-modern.error   .alert-icon { background: #FEE2E2; color: #DC2626; }
    .alert-title { font-size: 15px; font-weight: 700; margin-bottom: 4px; }

    /* TOKEN BOX */
    .token-box {
      background: var(--navy); border-radius: 12px;
      padding: 16px 20px; margin-top: 14px;
      display: flex; align-items: center; justify-content: space-between; gap: 12px;
    }
    .token-box .label { font-size: 11px; color: rgba(255,255,255,.45); text-transform: uppercase; letter-spacing: .8px; }
    .token-box .code  { font-size: 13px; font-weight: 700; color: #fff; letter-spacing: 1px; font-family: monospace; }
    .token-copy {
      background: rgba(255,255,255,.10); border: none; color: rgba(255,255,255,.70);
      border-radius: 8px; padding: 7px 12px; font-size: 12px; cursor: pointer;
      transition: background .15s;
    }
    .token-copy:hover { background: rgba(255,255,255,.18); color: #fff; }

    /* SIDE PANEL */
    .side-panel { background: var(--white); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); overflow: hidden; }
    .side-panel-header { background: var(--navy-soft); padding: 18px 22px; }
    .side-panel-header h3 { font-size: 14px; font-weight: 700; color: #fff; margin: 0; }
    .side-panel-body { padding: 20px 22px; }

    .status-item {
      display: flex; align-items: center; gap: 12px;
      padding: 12px 0; border-bottom: 1px solid var(--border);
    }
    .status-item:last-child { border-bottom: none; }
    .status-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .status-dot.green  { background: #10B981; box-shadow: 0 0 0 3px rgba(16,185,129,.20); }
    .status-dot.yellow { background: var(--gold); box-shadow: 0 0 0 3px rgba(245,158,11,.20); }
    .status-dot.red    { background: #EF4444; box-shadow: 0 0 0 3px rgba(239,68,68,.20); }
    .status-item-name  { font-size: 13px; font-weight: 600; color: var(--navy); }
    .status-item-sub   { font-size: 11px; color: var(--muted); }
    .status-item-badge { margin-left: auto; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; }

    .contact-block { background: var(--surface); border-radius: 12px; padding: 18px; margin-top: 18px; }
    .contact-block h4 { font-size: 13px; font-weight: 700; color: var(--navy); margin-bottom: 12px; }
    .contact-item { display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--muted); margin-bottom: 8px; }
    .contact-item i { color: var(--accent); width: 16px; }

    @media (max-width: 991px) {
      .sidebar { display: none; }
      .main-wrapper { margin-left: 0; }
      .page-content { padding: 20px; }
      .topbar { padding: 14px 20px; }
      .cat-grid { grid-template-columns: repeat(2, 1fr); }
    }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="logo-mark"><i class="bi bi-heart-fill"></i></div>
    <h1>SolidariApp</h1>
    <p>Plataforma solidaria</p>
  </div>
  <div class="sidebar-nav">
    <div class="nav-section">Principal</div>
    <a href="index.php" class="nav-link">
      <i class="bi bi-house-door"></i> Inicio
    </a>
    <a href="index.php#donacion" class="nav-link">
      <i class="bi bi-gift"></i> Nueva Donación
    </a>
    <div class="nav-section" style="margin-top:16px;">Herramientas</div>
    <a href="soporte.php" class="nav-link active">
      <i class="bi bi-headset"></i> Soporte Técnico
    </a>
    <a href="#" class="nav-link">
      <i class="bi bi-bar-chart-line"></i> Reportes
    </a>
    <a href="#" class="nav-link">
      <i class="bi bi-building-check"></i> Fundaciones
    </a>
    <a href="#" class="nav-link">
      <i class="bi bi-gear"></i> Configuración
    </a>
  </div>
  <div class="sidebar-footer">
    <p class="version">v1.0.0 — © 2025 SolidariApp</p>
  </div>
</aside>

<div class="main-wrapper">
  <header class="topbar">
    <div>
      <div class="topbar-title">Soporte Técnico</div>
      <div class="topbar-sub">Reporte de incidencias y fallas de la plataforma</div>
    </div>
    <div class="topbar-right">
      <a href="index.php" class="btn btn-sm" style="background:var(--surface);border:1.5px solid var(--border);border-radius:9px;font-size:13px;font-weight:600;color:var(--navy);padding:7px 14px;text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i> Volver al inicio
      </a>
      <div class="avatar">AD</div>
    </div>
  </header>

  <main class="page-content">

    <?php if ($mensaje): ?>
    <div class="alert-modern <?= $tipo_alerta === 'success' ? 'success' : 'error' ?> mb-4" role="alert">
      <div class="alert-icon">
        <i class="bi bi-<?= $tipo_alerta === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
      </div>
      <div style="flex:1;">
        <div class="alert-title">
          <?= $tipo_alerta === 'success' ? 'Ticket creado correctamente' : 'Error al enviar el reporte' ?>
        </div>
        <?= htmlspecialchars($mensaje) ?>
        <?php if ($token_generado): ?>
        <div class="token-box">
          <div>
            <div class="label">Token de seguimiento UUID</div>
            <div class="code" id="tokenCode"><?= htmlspecialchars($token_generado) ?></div>
          </div>
          <button class="token-copy" onclick="copyToken()">
            <i class="bi bi-clipboard me-1"></i> Copiar
          </button>
        </div>
        <p style="font-size:12px;color:#065F46;margin:10px 0 0;">
          <i class="bi bi-envelope-check me-1"></i>
          Se ha enviado el token a tu correo electrónico.
        </p>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="row g-4">

      <!-- FORM -->
      <div class="col-12 col-lg-8">
        <div class="form-card">
          <div class="form-card-header">
            <div class="hicon"><i class="bi bi-bug"></i></div>
            <div>
              <h2>Reportar Incidencia Técnica</h2>
              <p>Generaremos un token UUID único para el seguimiento de tu caso</p>
            </div>
          </div>
          <div class="form-card-body">
            <form action="soporte.php" method="POST" id="supportForm" novalidate>
              <input type="hidden" name="tipo_form" value="soporte"/>

              <!-- DATOS DE CONTACTO -->
              <div style="font-size:13px;font-weight:700;color:var(--navy);margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;">
                <i class="bi bi-person-badge" style="color:var(--accent);"></i> Datos de contacto
              </div>
              <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6">
                  <label class="form-label" for="nombre_contacto">Nombre completo</label>
                  <div class="input-group">
                    <span class="input-prefix"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="nombre_contacto" name="nombre_contacto"
                           placeholder="Tu nombre" required
                           value="<?= htmlspecialchars($_POST['nombre_contacto'] ?? '') ?>"/>
                  </div>
                </div>
                <div class="col-12 col-sm-6">
                  <label class="form-label" for="email_contacto">Correo electrónico</label>
                  <div class="input-group">
                    <span class="input-prefix"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email_contacto" name="email_contacto"
                           placeholder="correo@ejemplo.com" required
                           value="<?= htmlspecialchars($_POST['email_contacto'] ?? '') ?>"/>
                  </div>
                </div>
                <div class="col-12 col-sm-6">
                  <label class="form-label" for="telefono_contacto">Teléfono (opcional)</label>
                  <div class="input-group">
                    <span class="input-prefix"><i class="bi bi-telephone"></i></span>
                    <input type="tel" class="form-control" id="telefono_contacto" name="telefono_contacto"
                           placeholder="+57 300 0000000"
                           value="<?= htmlspecialchars($_POST['telefono_contacto'] ?? '') ?>"/>
                  </div>
                </div>
                <div class="col-12 col-sm-6">
                  <label class="form-label" for="rol_usuario">Tu rol en la plataforma</label>
                  <select class="form-select" id="rol_usuario" name="rol_usuario" required>
                    <option value="" disabled <?= empty($_POST['rol_usuario']) ? 'selected' : '' ?>>Seleccionar...</option>
                    <option value="donante"      <?= ($_POST['rol_usuario'] ?? '') === 'donante'      ? 'selected' : '' ?>>Donante</option>
                    <option value="fundacion"    <?= ($_POST['rol_usuario'] ?? '') === 'fundacion'    ? 'selected' : '' ?>>Representante de Fundación</option>
                    <option value="administrador"<?= ($_POST['rol_usuario'] ?? '') === 'administrador'? 'selected' : '' ?>>Administrador</option>
                    <option value="visitante"    <?= ($_POST['rol_usuario'] ?? '') === 'visitante'    ? 'selected' : '' ?>>Visitante</option>
                  </select>
                </div>
              </div>

              <!-- CATEGORÍA DE FALLO -->
              <div style="font-size:13px;font-weight:700;color:var(--navy);margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;">
                <i class="bi bi-grid-1x2" style="color:var(--accent);"></i> Categoría de la falla
              </div>
              <div class="cat-grid mb-4">
                <?php
                $cats = [
                  ['login',     'bi-lock',             'Acceso / Login'],
                  ['pago',      'bi-credit-card',      'Pagos'],
                  ['carga',     'bi-cloud-upload',      'Carga de datos'],
                  ['donacion',  'bi-gift',              'Donaciones'],
                  ['notif',     'bi-bell',              'Notificaciones'],
                  ['otro',      'bi-three-dots',        'Otro'],
                ];
                foreach ($cats as $c):
                  $checked = ($_POST['categoria_fallo'] ?? '') === $c[0] ? 'checked' : '';
                ?>
                <div>
                  <input class="cat-option" type="radio" name="categoria_fallo" id="cat_<?= $c[0] ?>" value="<?= $c[0] ?>" <?= $checked ?> required/>
                  <label class="cat-label" for="cat_<?= $c[0] ?>">
                    <i class="bi <?= $c[1] ?>"></i>
                    <?= $c[2] ?>
                  </label>
                </div>
                <?php endforeach; ?>
              </div>

              <!-- PRIORIDAD -->
              <div class="mb-4">
                <label class="form-label">Prioridad percibida</label>
                <div class="priority-row">
                  <?php
                  $prios = ['baja'=>'🟢 Baja','media'=>'🟡 Media','alta'=>'🔴 Alta','critica'=>'🔥 Crítica'];
                  foreach ($prios as $val => $lbl):
                    $checked = ($_POST['prioridad'] ?? 'media') === $val ? 'checked' : '';
                  ?>
                  <input class="priority-option" type="radio" name="prioridad" id="prio_<?= $val ?>" value="<?= $val ?>" <?= $checked ?>/>
                  <label class="priority-label" for="prio_<?= $val ?>"><?= $lbl ?></label>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- DESCRIPCIÓN -->
              <div style="font-size:13px;font-weight:700;color:var(--navy);margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;">
                <i class="bi bi-file-text" style="color:var(--accent);"></i> Descripción detallada
              </div>
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label" for="titulo_falla">Título del problema</label>
                  <input type="text" class="form-control" id="titulo_falla" name="titulo_falla"
                         placeholder="Ej: No puedo completar el proceso de pago" required
                         value="<?= htmlspecialchars($_POST['titulo_falla'] ?? '') ?>"/>
                </div>
                <div class="col-12">
                  <label class="form-label" for="descripcion_falla">Descripción detallada</label>
                  <textarea class="form-control" id="descripcion_falla" name="descripcion_falla"
                            rows="4" placeholder="Describe el error con detalle: ¿qué estabas haciendo?, ¿qué mensaje apareció?, ¿desde qué dispositivo?" required><?= htmlspecialchars($_POST['descripcion_falla'] ?? '') ?></textarea>
                  <div style="font-size:11px;color:var(--muted);margin-top:5px;">Mínimo 20 caracteres. Más detalle = resolución más rápida.</div>
                </div>
                <div class="col-12">
                  <label class="form-label" for="pasos_reproduccion">Pasos para reproducir el error (opcional)</label>
                  <textarea class="form-control" id="pasos_reproduccion" name="pasos_reproduccion"
                            rows="3" placeholder="1. Entré a la sección X&#10;2. Hice clic en Y&#10;3. El error apareció..."><?= htmlspecialchars($_POST['pasos_reproduccion'] ?? '') ?></textarea>
                </div>
              </div>

              <hr style="border-color:var(--border);margin:26px 0;"/>

              <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div style="font-size:12px;color:var(--muted);max-width:300px;line-height:1.5;">
                  <i class="bi bi-qr-code me-1" style="color:var(--accent);"></i>
                  Recibirás un <strong>token UUID único</strong> en tu correo para hacer seguimiento.
                </div>
                <button type="submit" class="btn-submit" id="submitBtn">
                  <i class="bi bi-send me-2"></i> Enviar reporte
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- SIDE PANEL -->
      <div class="col-12 col-lg-4">

        <!-- Estado del sistema -->
        <div class="side-panel mb-4">
          <div class="side-panel-header">
            <h3><i class="bi bi-activity me-2"></i>Estado del sistema</h3>
          </div>
          <div class="side-panel-body">
            <?php
            $systems = [
              ['dot'=>'green',  'name'=>'Plataforma web',    'sub'=>'Operacional',     'badge'=>'OK',       'badge_color'=>'#ECFDF5','badge_text'=>'#059669'],
              ['dot'=>'green',  'name'=>'Pasarela de pagos', 'sub'=>'Operacional',     'badge'=>'OK',       'badge_color'=>'#ECFDF5','badge_text'=>'#059669'],
              ['dot'=>'yellow', 'name'=>'Notificaciones',    'sub'=>'Degradado',       'badge'=>'LENTO',    'badge_color'=>'#FFFBEB','badge_text'=>'#D97706'],
              ['dot'=>'green',  'name'=>'Base de datos',     'sub'=>'Operacional',     'badge'=>'OK',       'badge_color'=>'#ECFDF5','badge_text'=>'#059669'],
              ['dot'=>'red',    'name'=>'API Externa',       'sub'=>'Mantenimiento',   'badge'=>'DOWN',     'badge_color'=>'#FEF2F2','badge_text'=>'#DC2626'],
            ];
            foreach ($systems as $s):
            ?>
            <div class="status-item">
              <div class="status-dot <?= $s['dot'] ?>"></div>
              <div>
                <div class="status-item-name"><?= $s['name'] ?></div>
                <div class="status-item-sub"><?= $s['sub'] ?></div>
              </div>
              <span class="status-item-badge" style="background:<?= $s['badge_color'] ?>;color:<?= $s['badge_text'] ?>;">
                <?= $s['badge'] ?>
              </span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Contacto directo -->
        <div class="side-panel">
          <div class="side-panel-header">
            <h3><i class="bi bi-telephone me-2"></i>Contacto directo</h3>
          </div>
          <div class="side-panel-body">
            <p style="font-size:13px;color:var(--muted);line-height:1.6;margin-bottom:16px;">
              Para casos críticos o urgentes comunícate directamente con nuestro equipo:
            </p>
            <div class="contact-block">
              <h4><i class="bi bi-clock me-1" style="color:var(--accent);"></i> Horario de atención</h4>
              <p style="font-size:13px;color:var(--muted);margin:0;">Lunes a viernes: 8:00 AM – 6:00 PM<br/>Sábados: 9:00 AM – 1:00 PM</p>
            </div>
            <div style="margin-top:14px;">
              <div class="contact-item"><i class="bi bi-envelope-fill"></i> soporte@solidariapp.co</div>
              <div class="contact-item"><i class="bi bi-telephone-fill"></i> +57 601 234 5678</div>
              <div class="contact-item"><i class="bi bi-whatsapp"></i> +57 310 000 0000</div>
              <div class="contact-item"><i class="bi bi-chat-dots-fill"></i> Chat en tiempo real</div>
            </div>
            <div style="margin-top:16px;padding:12px;background:#EFF6FF;border-radius:10px;font-size:12px;color:var(--accent);line-height:1.5;">
              <i class="bi bi-info-circle me-1"></i>
              Tiempo promedio de respuesta: <strong>2-4 horas hábiles</strong>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function copyToken() {
  const code = document.getElementById('tokenCode');
  if (code) {
    navigator.clipboard.writeText(code.textContent.trim()).then(() => {
      const btn = document.querySelector('.token-copy');
      btn.innerHTML = '<i class="bi bi-check2 me-1"></i> Copiado';
      setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copiar', 2000);
    });
  }
}

// Submit feedback
const form = document.getElementById('supportForm');
const submitBtn = document.getElementById('submitBtn');
form.addEventListener('submit', function(e) {
  if (form.checkValidity()) {
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Enviando...';
    submitBtn.disabled = true;
  }
  form.classList.add('was-validated');
});
</script>
</body>
</html>
