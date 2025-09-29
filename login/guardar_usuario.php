<?php
session_start();
include_once "../conexion.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Captura de datos con seguridad
    $Nombres   = strtoupper(trim($_POST['nombres'] ?? ''));
    $Apellidos = strtoupper(trim($_POST['apellidos'] ?? ''));
    $Correo    = strtolower(trim($_POST['email'] ?? ''));
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    
    // Validar contraseñas
    if ($password !== $password2) {
        echo "❌ Las contraseñas no coinciden. <a href='register.php'>Volver</a>";
        exit;
    }

    // Verificar si ya existe el usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $Correo]);
    
    if ($stmt->fetch()) {
        echo "Hola..";
        echo "❌ El correo ya está registrado. <a href='register.php'>Volver</a>";
        exit;
    }

    // Encriptar la contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar usuario
    /* $sql = "INSERT INTO usuarios (Nombres, Apellidos, Email, password) 
            VALUES (:nombre, :apellido, :email, :password)";
    $stmt = $pdo->prepare($sql);

    $ok = $stmt->execute([
        ':nombre'   => $Nombres,
        ':apellido' => $Apellidos,
        ':email'    => $Correo,
        ':password' => $password_hash
    ]); */

    if ($ok) {
        echo "✅ Usuario registrado correctamente. <a href='login.php'>Iniciar sesión</a>";
    } else {
        echo "❌ Error al registrar el usuario. Intenta de nuevo.";
    }
}
?>
