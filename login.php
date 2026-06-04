<?php
session_start();
require_once 'src/AuthManager.php';
require_once 'src/Database.php';

use SolidariApp\AuthManager;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = AuthManager::verificarLogin($_POST['email'], $_POST['pass']);
    if ($usuario) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        header("Location: index.php");
        exit;
    }
    $error = "Credenciales incorrectas.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Red Solidaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --azul-solidario: #1e52ff; --verde-solidario: #63ff5e; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .login-card { 
            border-radius: 28px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.1); 
            padding: 40px; width: 100%; max-width: 420px; 
        }
        .btn-custom { 
            background: linear-gradient(135deg, var(--azul-solidario), var(--verde-solidario)); 
            color: white; border: none; border-radius: 50px; padding: 12px; font-weight: 600; 
            transition: 0.4s; 
        }
        .btn-custom:hover { transform: scale(1.02); color: white; box-shadow: 0 5px 15px rgba(30, 82, 255, 0.3); }
        .form-control { border-radius: 12px; padding: 12px; border: 1px solid #eee; }
        .logo-img { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; margin-bottom: 20px; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

    <div class="card login-card text-center">
        <img src="Logo.jpeg" alt="Red Solidaria Logo" class="logo-img mx-auto">
        <h3 class="fw-bold mb-4">Iniciar Sesión</h3>
        
        <?php if($error): ?>
            <div class="alert alert-danger border-0 rounded-pill"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-4">
                <input type="password" name="pass" class="form-control" placeholder="Contraseña" required>
            </div>
            <button type="submit" class="btn btn-custom w-100">
                <i class="fas fa-sign-in-alt me-2"></i> Entrar
            </button>
        </form>
        <p class="text-center mt-4 text-muted">¿No tienes cuenta? <a href="registro.php" class="text-primary fw-bold text-decoration-none">Regístrate</a></p>
    </div>

</body>
</html>
