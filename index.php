<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }
require_once 'src/Database.php';
require_once 'logica.php';
use SolidariApp\Database;
use function SolidariApp\procesarDonacion;

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = procesarDonacion($_POST, $_SESSION['usuario_id']);
    $class = ($res['tipo'] == 'success') ? 'alert-success' : 'alert-warning';
    $msg = "<div class='alert $class'>{$res['mensaje']}</div>";
}

$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM donaciones WHERE usuario_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['usuario_id']]);
$donaciones = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
    <div class="container py-5 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></h2>
            <a href="logout.php" class="btn btn-danger btn-sm">Cerrar Sesión</a>
        </div>
        
        <?= $msg ?>
        
        <form method="POST" class="card p-4 mb-4 shadow-sm">
            <h4 class="mb-3">Realizar Donación</h4>
            <input name="nombre_contacto" class="form-control mb-2" placeholder="Nombre" required>
            <input name="email_contacto" class="form-control mb-2" placeholder="Email" required>
            <input name="monto" type="number" step="0.01" class="form-control mb-2" placeholder="Monto" required>
            <button class="btn btn-success">Donar ahora</button>
        </form>

        <div class="card p-4 shadow-sm">
            <h4>Mis Donaciones</h4>
            <table class="table mt-3">
                <thead>
                    <tr><th>Monto</th><th>Token</th><th>Fecha</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($donaciones as $d): ?>
                    <tr>
                        <td>$<?= number_format($d['monto'], 2) ?></td>
                        <td><small class="text-muted"><?= $d['token_uuid'] ?></small></td>
                        <td><?= $d['created_at'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-4 mt-auto">
        <div class="mb-2">
            <span class="badge bg-primary">PHP</span> <span class="badge bg-success">MongoDB</span> 
            <span class="badge bg-info">Render</span> <span class="badge bg-warning text-dark">Python</span> 
            <span class="badge bg-danger">Java</span>
        </div>
        <p class="mb-0">SolidariApp - ADSO | Desarrollador Junior: Will Morales</p>
    </footer>
</body>
</html>
