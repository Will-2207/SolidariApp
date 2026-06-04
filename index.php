<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }
require_once 'src/Database.php';

use SolidariApp\Database;

$db = Database::getConnection();

// Obtener datos para el dashboard
$stmt = $db->prepare("SELECT * FROM donaciones WHERE usuario_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['usuario_id']]);
$mis_donaciones = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>SolidariApp — Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  <style>
    :root { --navy: #0A1628; --accent: #2563EB; --surface: #F0F4FF; }
    body { background: var(--surface); font-family: sans-serif; }
    .sidebar { width: 260px; height: 100vh; background: var(--navy); position: fixed; color: white; padding: 20px; }
    .main-content { margin-left: 260px; padding: 40px; }
    .card { border-radius: 14px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 20px; }
  </style>
</head>
<body>
  <div class="sidebar">
    <h3>SolidariApp</h3>
    <hr>
    <a href="index.php" class="text-white text-decoration-none d-block py-2"><i class="bi bi-house"></i> Inicio</a>
    <a href="logout.php" class="text-white text-decoration-none d-block py-2"><i class="bi bi-box-arrow-right"></i> Salir</a>
  </div>

  <div class="main-content">
    <h2 class="mb-4">Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></h2>
    
    <div class="card mb-4">
      <h4>Mis Donaciones Registradas</h4>
      <table class="table mt-3">
        <thead>
          <tr><th>Monto</th><th>Token UUID</th><th>Fecha</th></tr>
        </thead>
        <tbody>
          <?php foreach($mis_donaciones as $d): ?>
            <tr>
              <td>$<?= number_format($d['monto'], 2) ?></td>
              <td><code><?= $d['token_uuid'] ?></code></td>
              <td><?= $d['created_at'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
