<?php
// =========================================================================
// MODO DE DEPURACIÓN ACTIVA (Borrar o comentar tras solucionar)
// =========================================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h3>[DEBUG] Iniciando script de recuperación...</h3>";

echo "[DEBUG] Intentando cargar conexion.php...<br>";
require_once 'conexion.php'; 
echo "[DEBUG] conexion.php cargada con éxito.<br>";

// Verificar qué variable de conexión creó tu archivo conexion.php
if (isset($db)) {
    echo "[DEBUG] Se detectó la variable de conexión: \$db<br>";
    $pdo = $db;
} elseif (isset($pdo)) {
    echo "[DEBUG] Se detectó la variable de conexión: \$pdo<br>";
} else {
    echo "<b style='color:red;'>[ERROR] No se encontró ninguna variable de conexión (\$db o \$pdo) tras cargar conexion.php</b><br>";
}

echo "[DEBUG] Intentando cargar EmailService.php...<br>";
require_once 'EmailService.php'; 
echo "[DEBUG] EmailService.php cargado con éxito.<br>";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    echo "[DEBUG] Email recibido del formulario: " . htmlspecialchars($email) . "<br>";

    if (!empty($email)) {
        try {
            echo "[DEBUG] Consultando si el usuario existe en la base de datos...<br>";
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE Email = :email");
            $stmt->execute([':email' => $email]);    
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                echo "<b style='color:green;'>[DEBUG] Usuario encontrado. ID: " . $usuario['id'] . "</b><br>";
                
                $temp_password = bin2hex(random_bytes(4)); 
                echo "[DEBUG] Contraseña temporal generada: " . $temp_password . "<br>";
                $password_hash = password_hash($temp_password, PASSWORD_DEFAULT);                        

                echo "[DEBUG] Actualizando contraseña en la BD...<br>";
                $stmt = $pdo->prepare("UPDATE usuarios SET password = :pass, password_reset_required = 1 WHERE Email = :email");
                $stmt->execute([':pass' => $password_hash, ':email' => $email]);        
                echo "[DEBUG] Base de datos actualizada con éxito.<br>";

                echo "[DEBUG] Inicializando EmailService e intentando cargar email_config.php de forma indirecta...<br>";
                $emailService = new EmailService();
                
                echo "[DEBUG] Intentando enviar el correo electrónico...<br>";
                $fueEnviado = $emailService->enviarRestablecimiento($email, $temp_password);                
                
                if ($fueEnviado) {
                    echo "<b style='color:green;'>[ÉXITO] El servicio de correo devolvió TRUE (Correo enviado).</b><br>";
                } else {
                    echo "<b style='color:orange;'>[ADVERTENCIA] El servicio de correo devolvió FALSE (No se envió).</b><br>";
                }
            } else {
                echo "<b style='color:red;'>[DEBUG] El usuario no existe en la base de datos.</b><br>";
            }

        } catch (\Exception $e) {
            echo "<h4 style='color:red;'>[CAPTURA DE EXCEPCIÓN] El sistema se detuvo por el siguiente error:</h4>";
            echo "<pre>" . $e->getMessage() . "</pre>";
            echo "<b>Archivo:</b> " . $e->getFile() . " en la línea " . $e->getLine() . "<br>";
        }
    } else {
        echo "[DEBUG] El campo de email llegó vacío.<br>";
    }
} else {
    echo "[DEBUG] El script no se ejecutó mediante POST. Método actual: " . $_SERVER["REQUEST_METHOD"] . "<br>";
}

echo "<hr><b>[DEBUG] Fin del script de prueba. La redirección automática ha sido desactivada para que puedas leer este reporte.</b>";
// header("Location: index.php"); // <--- Comentado temporalmente para depurar
exit();
?>