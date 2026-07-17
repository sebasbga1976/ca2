<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexion.php';
require_once 'EmailService.php';

// Asegurar compatibilidad si tu conexion.php define $db en lugar de $pdo
if (!isset($pdo) && isset($db)) {
    $pdo = $db;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (!empty($email)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE Email = :email");
            $stmt->execute([':email' => $email]);    
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                // Generar contraseña temporal de 8 caracteres
                $temp_password = bin2hex(random_bytes(4)); 
                $password_hash = password_hash($temp_password, PASSWORD_DEFAULT);                        

                // Actualizar la contraseña temporal y exigir cambio en el primer inicio de sesión
                $stmt = $pdo->prepare("UPDATE usuarios SET password = :pass, password_reset_required = 1 WHERE Email = :email");
                $stmt->execute([':pass' => $password_hash, ':email' => $email]);        

                // Enviar el correo
                $emailService = new EmailService();
                $emailService->enviarRestablecimiento($email, $temp_password);                
            }
            
            // Mensaje genérico por seguridad (evita que atacantes verifiquen qué correos existen)
            $_SESSION['recuperar_msg'] = "Si el correo coincide con una cuenta activa, recibirás un mensaje con tu nueva contraseña temporal en unos minutos.";
            $_SESSION['recuperar_tipo'] = "success";

        } catch (\Exception $e) {
            // Guardar el error en el log del servidor en vez de mostrárselo al usuario
            error_log("Error en recuperación de contraseña: " . $e->getMessage() . " en " . $e->getFile() . " Lín: " . $e->getLine());
            
            $_SESSION['recuperar_msg'] = "Ocurrió un inconveniente técnico al procesar tu solicitud. Por favor, inténtalo de nuevo más tarde.";
            $_SESSION['recuperar_tipo'] = "danger";
        }
    } else {
        $_SESSION['recuperar_msg'] = "Por favor, ingresa una dirección de correo electrónico válida.";
        $_SESSION['recuperar_tipo'] = "warning";
    }
}

// Redirección limpia al index
header("Location: index.php");
exit();