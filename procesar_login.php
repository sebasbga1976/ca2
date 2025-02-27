<?php
//conexion bd
$conn = require 'config/conexion.php';

// Obtener datos del formulario
$username = $_POST['username'];
$password = $_POST['password'];

// Validación
if (empty($username) || empty($password)) {
    echo "Por favor, complete todos los campos.";
    exit; // Detiene la ejecución si hay campos vacíos
}

if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
    echo "Nombre de usuario inválido.";
    exit;
}

if (strlen($password) < 8) {
    echo "La contraseña debe tener al menos 8 caracteres.";
    exit;
}

// Consulta preparada corregida
$sql = "SELECT Contraseña FROM Usuarios WHERE Usuario = ?";

$stmt = $conn->prepare($sql);

if ($stmt) {
    // Vincular parámetro
    $stmt->bind_param("s", $username); // "s" indica que el parámetro es una cadena

    // Ejecutar la consulta
    $stmt->execute();

    // Obtener el resultado
    $stmt->bind_result($hashedPassword); // Vincula el resultado a la variable $hashedPassword
    $stmt->fetch(); // Obtiene la fila resultante

    // Verificar la contraseña hasheada
    if (password_verify($password, $hashedPassword)) {
        // Inicio de sesión exitoso
        session_start();
        $_SESSION['username'] = $username; // Almacenar el nombre de usuario en la sesión
        $_SESSION['last_activity'] = time(); // Marca de tiempo de la última actividad
        $_SESSION['token'] = bin2hex(random_bytes(32)); // Generar token único
        header("Location: pagina_principal.php");
        exit;
    } else {
        // Inicio de sesión fallido
        echo "Usuario o contraseña incorrectos.";
    }

    // Cerrar la declaración y la conexión
    $stmt->close();
    $conn->close();
} else {
    // Error en la preparación de la consulta
    echo "Error en la consulta: " . $conn->error;
    $conn->close();
}
?>