<?php
require_once 'src/AuthManager.php';
require_once 'src/Database.php';

use SolidariApp\AuthManager;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $pass = $_POST['pass'] ?? '';

    if (AuthManager::registrarUsuario($nombre, $email, $pass)) {
        header("Location: login.php?msg=registrado");
        exit;
    } else {
        $error = "Error al registrar usuario.";
    }
}
?>
<form method="POST">
    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <input type="text" name="nombre" placeholder="Nombre completo" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="pass" placeholder="Contraseña" required>
    <button type="submit">Registrarse</button>
</form>
