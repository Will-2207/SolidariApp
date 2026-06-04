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
$donaciones = $db->prepare("SELECT * FROM donaciones WHERE usuario_id = ? ORDER BY created_at DESC");
$donaciones->execute([$_SESSION['usuario_id']]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
    <div class="container py-5 flex-grow-1">
        <h2>Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></h2>
        <?= $msg ?>
        <form method="POST" class="card p-3 mb-4 shadow-sm">
            <input name="nombre_contacto" class="form-control mb-2" placeholder="Nombre" required>
            <input name="email_contacto" class="form-control mb-2" placeholder="Email" required>
            <input name="monto" type="number" class="form-control mb-2" placeholder="Monto" required>
            <button class="btn btn-success">Donar ahora</button>
        </form>
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
