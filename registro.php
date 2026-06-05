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
    body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; }
    .main-container { width: 90%; max-width: 1100px; background: white; border-radius: 30px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.1); display: flex; min-height: 600px; }
    
    /* Lado Izquierdo - Aseguramos visibilidad */
    .left-side { flex: 1; position: relative; background: #eee; }
    .carousel, .carousel-inner, .carousel-item { height: 100%; width: 100%; }
    .carousel-item img { height: 600px; width: 100%; object-fit: cover; }
    
    /* Estilo del texto que mencionas */
    .carousel-caption { 
        background: rgba(0,0,0,0.5); 
        padding: 20px; 
        border-radius: 15px; 
        bottom: 20%; 
    }

    /* Derecha: Formulario */
    .right-side { flex: 0 0 450px; padding: 60px; display: flex; flex-direction: column; justify-content: center; }
    .btn-custom { background: linear-gradient(135deg, var(--azul-solidario), var(--verde-solidario)); color: white; border: none; border-radius: 50px; padding: 12px; font-weight: 600; }
    .logo-img { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; margin-bottom: 20px; }
</style>
</head>
<body>

<div class="main-container">
    <div class="left-side">
        <div id="registroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="3000">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="https://images.unsplash.com/photo-1511632765400-ca8d7099454a?q=80&w=1000" alt="Solidaridad">
                    <div class="carousel-caption"><h3>Sé parte del cambio</h3><p>Únete a nuestra red de apoyo social.</p></div>
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1531206715585-50a19e836109?q=80&w=1000" alt="Comunidad">
                    <div class="carousel-caption"><h3>Red Solidaria</h3><p>Donar es transformar realidades.</p></div>
                </div>
            </div>
        </div>
    </div>

    <div class="right-side">
        <img src="Logo.jpeg" alt="Logo" class="logo-img mx-auto">
        <h3 class="fw-bold mb-4 text-center">Crear Cuenta</h3>
        
        <?php if($error): ?>
            <div class="alert alert-danger border-0 rounded-pill"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <input type="text" name="nombre" class="form-control" placeholder="Nombre completo" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-4">
                <input type="password" name="pass" class="form-control" placeholder="Contraseña" required>
            </div>
            <button type="submit" class="btn btn-custom w-100">
                <i class="fas fa-user-plus me-2"></i> Registrarse
            </button>
        </form>
        <p class="text-center mt-4 text-muted">¿Ya tienes cuenta? <a href="login.php" class="text-primary fw-bold text-decoration-none">Inicia sesión</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Inicialización forzada del carrusel
    var myCarousel = document.querySelector('#registroCarousel');
    var carousel = new bootstrap.Carousel(myCarousel, { interval: 3000, ride: 'carousel' });
</script>
</body>
</html>
