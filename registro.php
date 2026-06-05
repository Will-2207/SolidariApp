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
    <meta charset="UTF-8">
    <title>Registro — SolidariApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    :root { --azul-solidario: #1e52ff; --verde-solidario: #63ff5e; }
    body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
    
    .main-container { 
        width: 90%; max-width: 1100px; background: white; border-radius: 30px; 
        overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.1); 
        display: flex; min-height: 600px; 
    }
    
    /* Izquierda: Animación de fondo suave entre foto1 y foto2 */
    .left-side { 
        flex: 1; 
        position: relative; 
        background-color: #1e52ff; 
        background-size: cover; 
        background-position: center;
        background-repeat: no-repeat;
        animation: cambiarFondo 12s infinite ease-in-out;
    }

    @keyframes cambiarFondo {
        0%, 45% { background-image: url('assets/foto1.jpg'); }
        50%, 95% { background-image: url('assets/foto2.jpg'); }
        100% { background-image: url('assets/foto1.jpg'); }
    }

    /* Capa para mejorar legibilidad del texto */
    .overlay-caption {
        position: absolute; bottom: 0; left: 0; right: 0;
        background: linear-gradient(transparent, rgba(0,0,0,0.7));
        padding: 60px 40px; color: white;
    }

    /* Derecha */
    .right-side { flex: 0 0 450px; padding: 60px; display: flex; flex-direction: column; justify-content: center; }
    .btn-custom { background: linear-gradient(135deg, var(--azul-solidario), var(--verde-solidario)); color: white; border: none; border-radius: 50px; padding: 12px; font-weight: 600; }
    .logo-img { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; margin-bottom: 20px; }
</style>
</head>
<body>

<div class="main-container">
    <div class="left-side">
        <div class="overlay-caption">
            <h3 class="fw-bold">Sé parte del cambio</h3>
            <p>Únete a nuestra red de apoyo social y transforma vidas con nosotros.</p>
        </div>
    </div>

    <div class="right-side">
        <img src="Logo.jpeg" alt="Logo" class="logo-img mx-auto">
        <h3 class="fw-bold mb-4 text-center">Crear Cuenta</h3>
        
        <?php if($error): ?>
            <div class="alert alert-danger border-0 rounded-pill"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3"><input type="text" name="nombre" class="form-control" placeholder="Nombre completo" required></div>
            <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
            <div class="mb-4"><input type="password" name="pass" class="form-control" placeholder="Contraseña" required></div>
            <button type="submit" class="btn btn-custom w-100"><i class="fas fa-user-plus me-2"></i> Registrarse</button>
        </form>
        <p class="text-center mt-4 text-muted">¿Ya tienes cuenta? <a href="login.php" class="text-primary fw-bold text-decoration-none">Inicia sesión</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
