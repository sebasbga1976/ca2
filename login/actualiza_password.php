<?php
session_start();
require_once '../conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit;
}

$mensaje = "";

// 1. SOLO procesamos si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Captura de datos
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = trim($_POST['new_password'] ?? '');
    $confirm_pass     = trim($_POST['confirm_password'] ?? '');

    // Validación básica
    if (empty($current_password) || empty($new_password) || empty($confirm_pass)) {
        $mensaje = "❌ Todos los campos son obligatorios.";
    } elseif ($new_password !== $confirm_pass) {
        $mensaje = "❌ Las contraseñas nuevas no coinciden.";
    } else {
        try {
            // Verificar password actual
            $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
            $user = $stmt->fetch();

            if ($user && password_verify($current_password, $user['password'])) {
                // Encriptar y actualizar
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE usuarios SET password = ?, password_reset_required = 0 WHERE id = ?");
                $update->execute([$new_password_hash, $_SESSION['usuario_id']]);

                header("Location: ../index.php?mensaje=ok");
                exit;
            } else {
                $mensaje = "❌ La contraseña actual es incorrecta.";
            }
        } catch (PDOException $e) {
            $mensaje = "❌ Error de conexión: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar Contraseña</title>
    <style>
        .password-container { max-width: 400px; margin: 2rem auto; padding: 1.5rem; border: 1px solid #ddd; border-radius: 8px; background: #fff; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input { width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 0.8rem; background-color: #3ba8bb; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .mensaje { color: red; margin-bottom: 10px; font-weight: bold; }
    </style>
</head>
<body>

<div class="password-container">
    <h2>Actualizar contraseña</h2>
    
    <?php if ($mensaje): ?>
        <p class="mensaje"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="current_password">Contraseña Actual:</label>
            <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
        </div>

        <div class="form-group">
            <label for="new_password">Nueva contraseña:</label>
            <input type="password" id="new_password" name="new_password" required autocomplete="new-password">
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirmar nueva contraseña:</label>
            <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
        </div>

        <button type="submit">Actualizar contraseña</button>
    </form>
</div>

</body>
</html>