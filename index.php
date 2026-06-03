<?php
session_start();
// Si no hay usuario logueado, redirige al login (suponiendo que existe)
if (!isset($_SESSION['usuario_id'])) {
    // Para pruebas, puedes dejar un ID fijo o redirigir
    $_SESSION['usuario_id'] = 1; 
}

require_once 'logica.php';

$mensaje = '';
$tipo_alerta = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo_form']) && $_POST['tipo_form'] === 'donacion') {
    // Pasamos los datos Y el ID del usuario de la sesión
    $resultado = procesarDonacion($_POST, $_SESSION['usuario_id']);
    $mensaje = $resultado['mensaje'];
    $tipo_alerta = $resultado['tipo'];
}
?>
// Incluir lógica si viene de un POST
$mensaje = '';
$tipo_alerta = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo_form']) && $_POST['tipo_form'] === 'donacion') {
    require_once 'logica.php';
    $resultado = procesarDonacion($_POST);
    $mensaje = $resultado['mensaje'];
    $tipo_alerta = $resultado['tipo'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SolidariApp — Plataforma de Donaciones</title>
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
      --shadow-lg: 0 12px 48px rgba(10,22,40,.16);
    }
    *, *::before, *::after { box-sizing: border-box; }
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--surface);
      color: var(--navy);
      min-height: 100vh;
      margin: 0;
    }

    /* ── SIDEBAR ── */
    .sidebar {
      position: fixed; top: 0; left: 0;
      width: 260px; height: 100vh;
      background: var(--navy);
      display: flex; flex-direction: column;
      z-index: 100;
      border-right: 1px solid rgba(255,255,255,.06);
    }
    .sidebar-brand {
      padding: 28px 24px 20px;
      border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .sidebar-brand .logo-mark {
      width: 38px; height: 38px;
      background: var(--accent);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; color: #fff;
      margin-bottom: 10px;
    }
    .sidebar-brand h1 {
      font-size: 17px; font-weight: 800;
      color: #fff; margin: 0; letter-spacing: -.3px;
    }
    .sidebar-brand p {
      font-size: 11px; color: rgba(255,255,255,.45);
      margin: 0; font-weight: 500; letter-spacing: .5px;
      text-transform: uppercase;
    }
    .nav-section {
      padding: 20px 14px 4px;
      font-size: 10px; font-weight: 700;
      color: rgba(255,255,255,.30);
      text-transform: uppercase; letter-spacing: 1px;
    }
    .sidebar-nav { flex: 1; padding: 8px 14px; overflow-y: auto; }
    .sidebar-nav .nav-link {
      display: flex; align-items: center; gap: 12px;
      color: rgba(255,255,255,.60);
      padding: 11px 14px;
      border-radius: 10px;
      font-size: 14px; font-weight: 500;
      text-decoration: none;
      transition: all .18s;
      margin-bottom: 3px;
    }
    .sidebar-nav .nav-link i { font-size: 18px; flex-shrink: 0; }
    .sidebar-nav .nav-link:hover {
      background: rgba(255,255,255,.07);
      color: #fff;
    }
    .sidebar-nav .nav-link.active {
      background: var(--accent);
      color: #fff;
      box-shadow: 0 4px 14px rgba(37,99,235,.40);
    }
    .sidebar-footer {
      padding: 16px 20px;
      border-top: 1px solid rgba(255,255,255,.08);
    }
    .sidebar-footer .version {
      font-size: 11px; color: rgba(255,255,255,.25);
      text-align: center;
    }

    /* ── MAIN ── */
    .main-wrapper {
      margin-left: 260px;
      min-height: 100vh;
      display: flex; flex-direction: column;
    }
    .topbar {
      background: var(--white);
      border-bottom: 1px solid var(--border);
      padding: 16px 36px;
      display: flex; align-items: center; justify-content: space-between;
      position: sticky; top: 0; z-index: 90;
    }
    .topbar-title { font-size: 15px; font-weight: 700; color: var(--navy); }
    .topbar-sub   { font-size: 12px; color: var(--muted); margin-top: 1px; }
    .topbar-right { display: flex; align-items: center; gap: 12px; }
    .avatar {
      width: 36px; height: 36px; border-radius: 50%;
      background: var(--accent);
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 13px; font-weight: 700;
    }

    .page-content { padding: 36px; flex: 1; }

    /* ── KPI CARDS ── */
    .kpi-card {
      background: var(--white);
      border-radius: var(--radius);
      padding: 24px;
      border: 1px solid var(--border);
      box-shadow: var(--shadow);
      position: relative; overflow: hidden;
    }
    .kpi-card::after {
      content: '';
      position: absolute; right: -20px; top: -20px;
      width: 90px; height: 90px;
      border-radius: 50%;
      opacity: .06;
    }
    .kpi-card.blue::after  { background: var(--accent); }
    .kpi-card.gold::after  { background: var(--gold); }
    .kpi-card.green::after { background: #10B981; }
    .kpi-icon {
      width: 44px; height: 44px; border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 20px; margin-bottom: 14px;
    }
    .kpi-icon.blue  { background: #EFF6FF; color: var(--accent); }
    .kpi-icon.gold  { background: #FFFBEB; color: var(--gold); }
    .kpi-icon.green { background: #ECFDF5; color: #10B981; }
    .kpi-value {
      font-size: 26px; font-weight: 800;
      color: var(--navy); line-height: 1;
    }
    .kpi-label {
      font-size: 12px; color: var(--muted);
      font-weight: 500; margin-top: 6px;
    }
    .kpi-badge {
      font-size: 11px; font-weight: 600;
      padding: 3px 8px; border-radius: 20px;
      display: inline-flex; align-items: center; gap: 4px;
    }
    .kpi-badge.up   { background: #ECFDF5; color: #059669; }
    .kpi-badge.down { background: #FEF2F2; color: #DC2626; }

    /* ── FORM CARD ── */
    .form-card {
      background: var(--white);
      border-radius: var(--radius);
      border: 1px solid var(--border);
      box-shadow: var(--shadow);
      overflow: hidden;
    }
    .form-card-header {
      background: var(--navy);
      padding: 22px 28px;
      display: flex; align-items: center; gap: 14px;
    }
    .form-card-header .hicon {
      width: 42px; height: 42px; border-radius: 10px;
      background: rgba(255,255,255,.10);
      display: flex; align-items: center; justify-content: center;
      font-size: 20px; color: #fff;
    }
    .form-card-header h2 {
      font-size: 16px; font-weight: 700;
      color: #fff; margin: 0;
    }
    .form-card-header p {
      font-size: 12px; color: rgba(255,255,255,.50);
      margin: 2px 0 0;
    }
    .form-card-body { padding: 28px; }

    /* ── FORM ELEMENTS ── */
    .form-label {
      font-size: 12px; font-weight: 700;
      color: var(--navy); text-transform: uppercase;
      letter-spacing: .6px; margin-bottom: 7px;
    }
    .form-control, .form-select {
      border: 1.5px solid var(--border);
      border-radius: 10px;
      padding: 11px 14px;
      font-size: 14px;
      color: var(--navy);
      font-family: 'Plus Jakarta Sans', sans-serif;
      transition: border-color .18s, box-shadow .18s;
    }
    .form-control:focus, .form-select:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(37,99,235,.12);
    }
    .form-control::placeholder { color: var(--muted); }
    textarea.form-control { resize: none; }

    .input-prefix {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-right: none;
      border-radius: 10px 0 0 10px;
      padding: 11px 14px;
      font-size: 14px; font-weight: 600;
      color: var(--muted);
    }
    .input-prefix + .form-control { border-radius: 0 10px 10px 0; }

    .btn-submit {
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 13px 28px;
      font-size: 14px; font-weight: 700;
      font-family: 'Plus Jakarta Sans', sans-serif;
      cursor: pointer;
      transition: background .18s, transform .12s, box-shadow .18s;
      box-shadow: 0 4px 14px rgba(37,99,235,.35);
    }
    .btn-submit:hover {
      background: #1D4ED8;
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(37,99,235,.45);
    }
    .btn-submit:active { transform: translateY(0); }

    /* ── PAYMENT GATEWAY ── */
    .gateway-card {
      background: linear-gradient(135deg, var(--navy) 0%, var(--navy-soft) 100%);
      border-radius: var(--radius);
      padding: 28px;
      color: #fff;
      position: relative; overflow: hidden;
    }
    .gateway-card::before {
      content: '';
      position: absolute; right: -60px; bottom: -60px;
      width: 220px; height: 220px;
      border-radius: 50%;
      background: rgba(255,255,255,.04);
    }
    .gateway-card::after {
      content: '';
      position: absolute; right: 40px; bottom: -80px;
      width: 160px; height: 160px;
      border-radius: 50%;
      background: rgba(37,99,235,.18);
    }
    .gateway-card .title {
      font-size: 13px; font-weight: 700;
      text-transform: uppercase; letter-spacing: 1px;
      color: rgba(255,255,255,.55); margin-bottom: 4px;
    }
    .gateway-card h3 {
      font-size: 20px; font-weight: 800;
      margin: 0 0 22px;
    }
    .card-visual {
      background: rgba(255,255,255,.08);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 18px;
      border: 1px solid rgba(255,255,255,.10);
      position: relative; z-index: 1;
    }
    .card-chip {
      width: 38px; height: 28px;
      background: linear-gradient(135deg, #F59E0B, #D97706);
      border-radius: 6px; margin-bottom: 16px;
    }
    .card-number {
      font-size: 15px; font-weight: 600;
      letter-spacing: 3px; color: rgba(255,255,255,.75);
      margin-bottom: 14px;
    }
    .card-meta {
      display: flex; gap: 20px;
    }
    .card-meta-item label {
      font-size: 9px; text-transform: uppercase;
      letter-spacing: 1px; color: rgba(255,255,255,.40);
      display: block; margin-bottom: 2px;
    }
    .card-meta-item span {
      font-size: 13px; font-weight: 600;
      color: rgba(255,255,255,.85);
    }
    .gateway-methods {
      display: flex; gap: 8px; flex-wrap: wrap;
      position: relative; z-index: 1;
    }
    .method-badge {
      background: rgba(255,255,255,.10);
      border: 1px solid rgba(255,255,255,.15);
      border-radius: 8px;
      padding: 6px 12px;
      font-size: 12px; font-weight: 600;
      color: rgba(255,255,255,.80);
      display: flex; align-items: center; gap: 6px;
    }
    .method-badge.active {
      background: var(--accent);
      border-color: var(--accent);
      color: #fff;
      box-shadow: 0 3px 10px rgba(37,99,235,.45);
    }
    .secure-badge {
      display: flex; align-items: center; gap: 6px;
      font-size: 11px; color: rgba(255,255,255,.45);
      margin-top: 16px; position: relative; z-index: 1;
    }
    .secure-badge i { color: #10B981; }

    /* ── ALERT ── */
    .alert-modern {
      border-radius: 12px; border: none;
      padding: 16px 20px;
      display: flex; align-items: flex-start; gap: 14px;
      font-size: 14px; font-weight: 500;
    }
    .alert-modern .alert-icon {
      width: 36px; height: 36px; border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; flex-shrink: 0;
    }
    .alert-modern.success { background: #ECFDF5; color: #065F46; }
    .alert-modern.success .alert-icon { background: #D1FAE5; color: #059669; }
    .alert-modern.error   { background: #FEF2F2; color: #991B1B; }
    .alert-modern.error   .alert-icon { background: #FEE2E2; color: #DC2626; }
    .alert-title { font-size: 15px; font-weight: 700; margin-bottom: 2px; }

    /* ── STEP INDICATOR ── */
    .steps-bar {
      display: flex; gap: 0; margin-bottom: 28px;
    }
    .step {
      flex: 1; text-align: center; position: relative;
    }
    .step::after {
      content: ''; position: absolute;
      top: 18px; left: 50%; width: 100%; height: 2px;
      background: var(--border);
    }
    .step:last-child::after { display: none; }
    .step-circle {
      width: 36px; height: 36px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 13px; font-weight: 700;
      margin: 0 auto 8px; position: relative; z-index: 1;
    }
    .step.done .step-circle   { background: var(--accent); color: #fff; }
    .step.active .step-circle { background: var(--navy); color: #fff; box-shadow: 0 0 0 4px rgba(10,22,40,.12); }
    .step.wait .step-circle   { background: var(--surface); color: var(--muted); border: 2px solid var(--border); }
    .step-label { font-size: 11px; font-weight: 600; color: var(--muted); }
    .step.active .step-label  { color: var(--navy); }
    .step.done .step-label    { color: var(--accent); }

    /* ── RESPONSIVE ── */
    @media (max-width: 991px) {
      .sidebar { display: none; }
      .main-wrapper { margin-left: 0; }
      .page-content { padding: 20px; }
      .topbar { padding: 14px 20px; }
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
    <a href="index.php" class="nav-link active">
      <i class="bi bi-house-door"></i> Inicio
    </a>
    <a href="index.php#donacion" class="nav-link">
      <i class="bi bi-gift"></i> Nueva Donación
    </a>
    <div class="nav-section" style="margin-top:16px;">Herramientas</div>
    <a href="soporte.php" class="nav-link">
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

<!-- MAIN WRAPPER -->
<div class="main-wrapper">

  <!-- TOPBAR -->
  <header class="topbar">
    <div>
      <div class="topbar-title">Panel de Donaciones</div>
      <div class="topbar-sub">Gestiona y registra aportes solidarios</div>
    </div>
    <div class="topbar-right">
      <button class="btn btn-sm" style="background:var(--surface);border:1.5px solid var(--border);border-radius:9px;font-size:13px;font-weight:600;color:var(--navy);padding:7px 14px;">
        <i class="bi bi-plus me-1"></i> Nueva donación
      </button>
      <div class="avatar">AD</div>
    </div>
  </header>

  <!-- PAGE CONTENT -->
  <main class="page-content">

    <?php if ($mensaje): ?>
    <div class="alert-modern <?= $tipo_alerta === 'success' ? 'success' : 'error' ?> mb-4" role="alert">
      <div class="alert-icon">
        <i class="bi bi-<?= $tipo_alerta === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
      </div>
      <div>
        <div class="alert-title"><?= $tipo_alerta === 'success' ? '¡Donación registrada!' : 'Error al procesar' ?></div>
        <?= htmlspecialchars($mensaje) ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- KPI ROW -->
    <div class="row g-4 mb-4">
      <div class="col-12 col-md-4">
        <div class="kpi-card blue">
          <div class="kpi-icon blue"><i class="bi bi-currency-dollar"></i></div>
          <div class="kpi-value">$48,320</div>
          <div class="kpi-label">Donaciones monetarias este mes</div>
          <div class="mt-2"><span class="kpi-badge up"><i class="bi bi-arrow-up-short"></i>+12.4%</span></div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="kpi-card gold">
          <div class="kpi-icon gold"><i class="bi bi-box-seam"></i></div>
          <div class="kpi-value">1,247</div>
          <div class="kpi-label">Aportes en especie registrados</div>
          <div class="mt-2"><span class="kpi-badge up"><i class="bi bi-arrow-up-short"></i>+8.1%</span></div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="kpi-card green">
          <div class="kpi-icon green"><i class="bi bi-building-check"></i></div>
          <div class="kpi-value">34</div>
          <div class="kpi-label">Fundaciones activas vinculadas</div>
          <div class="mt-2"><span class="kpi-badge up"><i class="bi bi-arrow-up-short"></i>+2 nuevas</span></div>
        </div>
      </div>
    </div>

    <!-- FORM + GATEWAY ROW -->
    <div class="row g-4" id="donacion">
      <!-- FORM -->
      <div class="col-12 col-lg-7">
        <div class="form-card">
          <div class="form-card-header">
            <div class="hicon"><i class="bi bi-gift"></i></div>
            <div>
              <h2>Registrar Donación</h2>
              <p>Completa los datos del aporte solidario</p>
            </div>
          </div>
          <div class="form-card-body">

            <!-- STEPS -->
            <div class="steps-bar">
              <div class="step done">
                <div class="step-circle"><i class="bi bi-check-lg"></i></div>
                <div class="step-label">Donante</div>
              </div>
              <div class="step active">
                <div class="step-circle">2</div>
                <div class="step-label">Aporte</div>
              </div>
              <div class="step wait">
                <div class="step-circle">3</div>
                <div class="step-label">Pago</div>
              </div>
              <div class="step wait">
                <div class="step-circle">4</div>
                <div class="step-label">Confirmación</div>
              </div>
            </div>

            <form action="index.php#donacion" method="POST" id="donationForm" novalidate>
              <input type="hidden" name="tipo_form" value="donacion"/>

              <div class="row g-3">
  <div class="col-12">
    <label class="form-label" for="nombre">Nombre del donante</label>
    <div class="input-group">
      <span class="input-prefix"><i class="bi bi-person"></i></span>
      <input type="text" class="form-control" id="nombre" name="nombre_contacto"
             placeholder="Ej. María González" required
             value="<?= htmlspecialchars($_POST['nombre_contacto'] ?? '') ?>"/>
    </div>
  </div>

  <div class="col-12">
    <label class="form-label" for="email">Correo electrónico</label>
    <div class="input-group">
      <span class="input-prefix"><i class="bi bi-envelope"></i></span>
      <input type="email" class="form-control" id="email" name="email_contacto"
             placeholder="Ej. correo@ejemplo.com" required
             value="<?= htmlspecialchars($_POST['email_contacto'] ?? '') ?>"/>
    </div>
  </div>
  

                <div class="col-12 col-sm-6">
                  <label class="form-label" for="tipo_ayuda">Tipo de ayuda</label>
                  <select class="form-select" id="tipo_ayuda" name="tipo_ayuda" required>
                    <option value="" disabled <?= empty($_POST['tipo_ayuda']) ? 'selected' : '' ?>>Seleccionar...</option>
                    <option value="monetaria"  <?= ($_POST['tipo_ayuda'] ?? '') === 'monetaria'  ? 'selected' : '' ?>>💵 Monetaria</option>
                    <option value="ropa"       <?= ($_POST['tipo_ayuda'] ?? '') === 'ropa'       ? 'selected' : '' ?>>👕 Ropa y vestimenta</option>
                    <option value="alimentos"  <?= ($_POST['tipo_ayuda'] ?? '') === 'alimentos'  ? 'selected' : '' ?>>🥫 Alimentos y víveres</option>
                    <option value="medicinas"  <?= ($_POST['tipo_ayuda'] ?? '') === 'medicinas'  ? 'selected' : '' ?>>💊 Medicamentos</option>
                    <option value="tecnologia" <?= ($_POST['tipo_ayuda'] ?? '') === 'tecnologia' ? 'selected' : '' ?>>💻 Tecnología</option>
                    <option value="otros"      <?= ($_POST['tipo_ayuda'] ?? '') === 'otros'      ? 'selected' : '' ?>>📦 Otros recursos</option>
                  </select>
                </div>

                <div class="col-12 col-sm-6">
                  <label class="form-label" for="monto">Monto (COP)</label>
                  <div class="input-group">
                    <span class="input-prefix">$</span>
                    <input type="number" class="form-control" id="monto" name="monto"
                           placeholder="0.00" min="0" step="1000"
                           value="<?= htmlspecialchars($_POST['monto'] ?? '') ?>"/>
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label" for="descripcion">Descripción del aporte</label>
                  <textarea class="form-control" id="descripcion" name="descripcion"
                            rows="3" placeholder="Describe brevemente tu donación…" required><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="col-12">
                  <label class="form-label" for="fundacion">Fundación destino</label>
                  <select class="form-select" id="fundacion" name="fundacion">
                    <option value="" disabled selected>Seleccionar fundación...</option>
                    <option value="fundacion_amor">Fundación Amor y Esperanza</option>
                    <option value="fundacion_niños">Fundación por los Niños</option>
                    <option value="fundacion_adultos">Fundación Adultos Mayores</option>
                    <option value="fundacion_animales">Fundación Animal</option>
                  </select>
                </div>
              </div>

              <hr style="border-color:var(--border);margin:24px 0;"/>

              <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div style="font-size:12px;color:var(--muted);">
                  <i class="bi bi-shield-check me-1" style="color:#10B981;"></i>
                  Tus datos están protegidos y cifrados
                </div>
                <button type="submit" class="btn-submit">
                  <i class="bi bi-send me-2"></i> Registrar donación
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- PAYMENT GATEWAY -->
      <div class="col-12 col-lg-5">
        <div class="gateway-card mb-4">
          <div class="title">Pasarela de pagos</div>
          <h3>Pago seguro<br/>certificado</h3>
          <div class="card-visual">
            <div class="card-chip"></div>
            <div class="card-number">•••• •••• •••• 4821</div>
            <div class="card-meta">
              <div class="card-meta-item">
                <label>Titular</label>
                <span>DONANTE SOLIDARIO</span>
              </div>
              <div class="card-meta-item">
                <label>Vence</label>
                <span>12/27</span>
              </div>
            </div>
          </div>
          <div class="gateway-methods">
            <div class="method-badge active"><i class="bi bi-credit-card"></i> Crédito</div>
            <div class="method-badge"><i class="bi bi-bank"></i> PSE</div>
            <div class="method-badge"><i class="bi bi-phone"></i> Nequi</div>
            <div class="method-badge"><i class="bi bi-cash"></i> Efectivo</div>
          </div>
          <div class="secure-badge">
            <i class="bi bi-patch-check-fill"></i>
            Transacciones procesadas con cifrado SSL/TLS
          </div>
        </div>

        <!-- Info card -->
        <div style="background:var(--white);border-radius:var(--radius);padding:22px;border:1px solid var(--border);box-shadow:var(--shadow);">
          <div style="font-size:13px;font-weight:700;color:var(--navy);margin-bottom:14px;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-info-circle" style="color:var(--accent);"></i> ¿Cómo funciona?
          </div>
          <div style="display:flex;flex-direction:column;gap:12px;">
            <?php
            $steps = [
              ['bi-1-circle','Completa el formulario con tus datos y el tipo de donación.'],
              ['bi-2-circle','Selecciona la fundación destino que deseas apoyar.'],
              ['bi-3-circle','Confirma el pago y recibe tu certificado de donación.'],
            ];
            foreach ($steps as $s): ?>
            <div style="display:flex;gap:10px;align-items:flex-start;">
              <i class="bi <?= $s[0] ?>" style="color:var(--accent);font-size:18px;flex-shrink:0;margin-top:1px;"></i>
              <span style="font-size:13px;color:var(--muted);line-height:1.5;"><?= $s[1] ?></span>
            </div>
            <?php endforeach; ?>
          </div>
          <div style="margin-top:18px;padding-top:16px;border-top:1px solid var(--border);">
            <a href="soporte.php" style="font-size:13px;color:var(--accent);font-weight:600;text-decoration:none;display:flex;align-items:center;gap:6px;">
              <i class="bi bi-headset"></i> ¿Problemas? Contacta soporte técnico
            </a>
          </div>
        </div>
      </div>
    </div>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Highlight payment method on click
document.querySelectorAll('.method-badge').forEach(badge => {
  badge.addEventListener('click', () => {
    document.querySelectorAll('.method-badge').forEach(b => b.classList.remove('active'));
    badge.classList.add('active');
  });
});

// Basic client-side validation feedback
const form = document.getElementById('donationForm');
form.addEventListener('submit', function(e) {
  const btn = form.querySelector('.btn-submit');
  if (form.checkValidity()) {
    btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Procesando...';
    btn.disabled = true;
  }
  form.classList.add('was-validated');
});
</script>
</body>
</html>
