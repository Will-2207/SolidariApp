<?php
require_once 'src/Database.php';
require_once 'src/AuthManager.php';
use SolidariApp\AuthManager;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (AuthManager::registrarUsuario($_POST['nombre'], $_POST['email'], $_POST['pass'])) {
        header("Location: login.php?msg=ok");
        exit;
    }
    $error = "Error al registrar usuario.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Registro — SolidariApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card p-4 shadow" style="width: 400px;">
        <h3 class="mb-3">Registro</h3>
        <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="nombre" class="form-control mb-2" placeholder="Nombre" required>
            <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
            <input type="password" name="pass" class="form-control mb-3" placeholder="Contraseña" required>
            <button type="submit" class="btn btn-primary w-100">Registrarse</button>
        </form>
    </div>
</body>
</html>
