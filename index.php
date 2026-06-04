<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }
require_once 'src/Database.php';
require_once 'logica.php'; // Incluimos la lógica de doble persistencia

use SolidariApp\Database;
use function SolidariApp\procesarDonacion;

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = procesarDonacion($_POST, $_SESSION['usuario_id']);
    $mensaje = "<div class='alert alert-info'>{$resultado['mensaje']}</div>";
}

$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM donaciones WHERE usuario_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['usuario_id']]);
$mis_donaciones = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>SolidariApp — Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2>Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></h2>
    <?= $mensaje ?>

    <div class="card p-4 mb-4">
      <h4>Realizar Donación</h4>
      <form method="POST">
        <input type="text" name="nombre_contacto" class="form-control mb-2" placeholder="Nombre" required>
        <input type="email" name="email_contacto" class="form-control mb-2" placeholder="Email" required>
        <input type="number" name="monto" class="form-control mb-2" placeholder="Monto" required>
        <button type="submit" class="btn btn-success">Donar ahora</button>
      </form>
    </div>

    <div class="card p-4">
      <h4>Mis Donaciones</h4>
      <table class="table">
        <thead><tr><th>Monto</th><th>Token</th><th>Fecha</th></tr></thead>
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
