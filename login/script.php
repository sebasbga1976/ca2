<?php
// 1. Iniciar sesión al principio (antes de cualquier salida de texto)
session_start();

include_once __DIR__ . "/../conexion.php";

// 2. Limpieza básica de datos
$email = trim($_POST['email'] ?? '');
$password_ingresada = $_POST['password'] ?? '';

if (empty($email) || empty($password_ingresada)) {
    // Redirigir con un mensaje de error (puedes capturarlo con $_GET['error'])
    header("Location: ../index.php?error=campos_vacios");
    exit;
}

try {
    // 3. Consulta preparada corregida
    $sql = "SELECT id, password, Nombres, Apellidos, password_reset_required FROM usuarios WHERE email = ? and `activo`=1 LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]); // Corregido: antes decía $SQL
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        // 4. Verificación lógica unificada
        if ($usuario && password_verify($password_ingresada, $usuario['password'])) {            
            // Regenerar ID de sesión por seguridad (evita fijación de sesión)
            session_regenerate_id(true);        
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = trim($usuario['Nombres'] . ' ' . $usuario['Apellidos']);        

            if ($usuario['password_reset_required'] == 1) {
                // Redirigir a una página de "Cambiar contraseña obligatoria"
                header("Location: actualiza_password.php");
                exit();
            }

            // 5. Redirección limpia
            header("Location: ../menu.php");
            exit; // Siempre usa exit después de un header Location
            
        } else {            
            // Mensaje genérico por seguridad
            header("Location: ../index.php?error=auth_failed");
            exit;
        }

} catch (PDOException $e) {
    error_log("Error en Login: " . $e->getMessage());
    header("Location: ../index.php?error=db_error");
    exit;
}