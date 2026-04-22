<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include_once '../conexion.php';
    require_once '../EmailService.php';

    // 1. Recibir y sanitizar datos
    $Nombres   = strtoupper(trim($_POST['nombres'] ?? ''));
    $Apellidos = strtoupper(trim($_POST['apellidos'] ?? ''));
    $Correo    = strtolower(trim($_POST['email'] ?? ''));
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    
    $nombre_completo = $Nombres . ' ' . $Apellidos;

    // Validación básica: campos vacíos
    if (empty($Nombres) || empty($Apellidos) || empty($Correo) || empty($password)) {
        die("❌ Todos los campos son obligatorios. <a href='register.php'>Volver</a>");
    }

    // 2. Validar contraseñas
    if ($password !== $password2) {
        die("❌ Las contraseñas no coinciden. <a href='register.php'>Volver</a>");
    }

    // 3. Verificar si el usuario ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $Correo]);
        
    if ($stmt->fetch()) {
        die("❌ El correo ya está registrado. <a href='register.php'>Volver</a>");
    }

    // 4. Inserción
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO usuarios (Nombres, Apellidos, Email, password) 
                VALUES (:nombres, :apellidos, :email, :password)";    
        $stmt = $pdo->prepare($sql);
        
        $resultado = $stmt->execute([
            ':nombres'   => $Nombres,
            ':apellidos' => $Apellidos,
            ':email'     => $Correo,
            ':password'  => $password_hash
        ]);

        if ($resultado) {
            // Intentamos enviar el correo
            try {
                $emailService = new EmailService();
                $emailService->enviarBienvenida($Correo, $nombre_completo);
            } catch (Exception $e) {
                // Registramos el error internamente sin interrumpir el registro
                error_log("Error al enviar correo de bienvenida a $Correo: " . $e->getMessage());
            }
            
            // Redirección exitosa
            header("Location: ../index.php");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Error en DB: " . $e->getMessage());
        die("❌ Hubo un error al procesar el registro. Intenta más tarde.");
    }

} else {
    echo "<h1>El archivo está cargando correctamente.</h1>";
    echo "<p>Esperando datos del formulario...</p>";
}
?>