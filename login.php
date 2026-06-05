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
    <title>Bienvenido a Red Solidaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --azul-solidario: #1e52ff; --verde-solidario: #63ff5e; }
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; height: 100vh; display: flex; align-items: center; }
        .main-container { width: 90%; max-width: 1100px; background: white; border-radius: 30px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.1); display: flex; min-height: 600px; }
        
        /* Izquierda: Carrusel */
        .left-side { flex: 1; background: #000; position: relative; }
        .carousel-item img { height: 600px; object-fit: cover; opacity: 0.7; }
        .carousel-caption { bottom: 50px; text-shadow: 0 2px 10px rgba(0,0,0,0.5); }

        /* Derecha: Login */
        .right-side { flex: 0 0 450px; padding: 60px; display: flex; flex-direction: column; justify-content: center; }
        .btn-custom { background: linear-gradient(135deg, var(--azul-solidario), var(--verde-solidario)); color: white; border: none; border-radius: 50px; padding: 12px; font-weight: 600; }
        .logo-img { width: 100px; border-radius: 50%; margin-bottom: 20px; }
    </style>
</head>
<body class="justify-content-center">

<div class="main-container">
    <div class="left-side">
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="https://images.unsplash.com/photo-1488521787171-252541a50a2e?q=80&w=1000" class="d-block w-100" alt="Ayuda">
                    <div class="carousel-caption"><h3>Transformando vidas</h3><p>Tu donación construye un futuro mejor.</p></div>
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1594708767771-a7502209ff51?q=80&w=1000" class="d-block w-100" alt="Niños">
                    <div class="carousel-caption"><h3>Sonrisas Solidarias</h3><p>Cada aporte cuenta una historia nueva.</p></div>
                </div>
            </div>
        </div>
    </div>

    <div class="right-side">
        <img src="Logo.jpeg" alt="Logo" class="logo-img mx-auto">
        <h3 class="fw-bold mb-4">Iniciar Sesión</h3>
        <?php if($error): ?><div class="alert alert-danger border-0 rounded-pill"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-4">
                <input type="password" name="pass" class="form-control" placeholder="Contraseña" required>
            </div>
            <button type="submit" class="btn btn-custom w-100">Entrar</button>
        </form>
        <p class="text-center mt-4 text-muted">¿No tienes cuenta? <a href="registro.php" class="text-primary fw-bold text-decoration-none">Regístrate</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
