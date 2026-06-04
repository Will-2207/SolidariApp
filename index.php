<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

require_once 'src/Database.php';
require_once 'logica.php'; // Asegúrate que aquí esté la función procesarDonacion corregida

use SolidariApp\Database;

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = \SolidariApp\procesarDonacion($_POST, $_SESSION['usuario_id']);
    $class = ($res['tipo'] == 'success') ? 'alert-success' : 'alert-warning';
    $msg = "<div class='alert $class border-0 shadow-sm rounded-pill mt-3'>{$res['mensaje']}</div>";
}

// Obtener historial de PostgreSQL
$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM donaciones WHERE usuario_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['usuario_id']]);
$donaciones = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel — SolidariApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --azul-solidario: #1e52ff; --verde-solidario: #63ff5e; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .header-dash { background: linear-gradient(135deg, var(--azul-solidario), var(--verde-solidario)); color: white; padding: 40px; border-radius: 28px; margin-bottom: 30px; }
        .card-custom { border-radius: 28px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.05); padding: 30px; }
        .btn-custom { background: linear-gradient(135deg, var(--azul-solidario), var(--verde-solidario)); color: white; border: none; border-radius: 50px; padding: 12px; font-weight: 600; }
        .table tr { background: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#"><img src="Logo.jpeg" width="40" class="me-2 rounded-circle">SolidariApp</a>
        <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a>
    </div>
</nav>

<div class="container py-4">
    <div class="header-dash">
        <h2 class="fw-bold">¡Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>!</h2>
        <p class="mb-0">Tu generosidad ayuda a cambiar vidas. ❤️</p>
    </div>

    <?= $msg ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <form method="POST" class="card card-custom">
                <h4 class="mb-4"><i class="fas fa-hand-holding-heart me-2"></i>Realizar Donación</h4>
                <input name="nombre_contacto" class="form-control mb-3 rounded-pill" placeholder="Nombre" required>
                <input name="email_contacto" class="form-control mb-3 rounded-pill" placeholder="Email" required>
                <input name="monto" type="number" step="0.01" class="form-control mb-3 rounded-pill" placeholder="Monto $" required>
                <button class="btn btn-custom w-100">Donar ahora</button>
            </form>
        </div>

        <div class="col-lg-8">
            <div class="card card-custom">
                <h4 class="mb-4"><i class="fas fa-history me-2"></i>Historial de Donaciones</h4>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Monto</th><th>Token</th><th>Fecha</th></tr></thead>
                        <tbody>
                            <?php foreach ($donaciones as $d): ?>
                            <tr>
                                <td class="fw-bold text-primary">$<?= number_format($d['monto'], 2) ?></td>
                                <td><span class="badge bg-light text-dark font-monospace"><?= substr($d['token_uuid'], 0, 15) ?>...</span></td>
                                <td class="text-muted small"><?= $d['created_at'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="text-center py-4 mt-5 text-muted small">
    <p>© 2026 SolidariApp | Desarrollador: <strong>William Morales</strong></p>
</footer>
</body>
</html>
