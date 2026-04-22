<?php
include_once 'conexion.php'; // Tu archivo de conexión
require_once 'EmailService.php'; // 1. Incluimos el servicio

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    // 1. Buscar si el usuario existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE Email = :email");
    $stmt->execute([':email' => $email]);    
    $usuario = $stmt->fetch();

    if ($usuario) {
        // 2. Generar contraseña temporal (8 caracteres aleatorios)
        $temp_password = bin2hex(random_bytes(4)); 
        $password_hash = password_hash($temp_password, PASSWORD_DEFAULT);                        

        // 3. Actualizar DB: nueva pass y marcar que debe cambiarla
        $stmt = $pdo->prepare("UPDATE usuarios SET password = :pass, password_reset_required = 1 WHERE Email = :email");
        $stmt->execute([':pass'  => $password_hash, ':email' => $email]);        

        $emailService = new EmailService();
        $fueEnviado = $emailService->enviarRestablecimiento($email, $temp_password);                
        
    } 
}
header("Location: index.php");
exit
?>