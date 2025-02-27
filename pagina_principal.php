<?php
session_start(); // Iniciar la sesión

// Tiempo de inactividad permitido (10 minutos)
$inactive_time = 600; // 10 minutos * 60 segundos

// Verificar si el usuario ha iniciado sesión
if (isset($_SESSION['username'])) {
    // Verificar el tiempo de inactividad
    if (time() - $_SESSION['last_activity'] > $inactive_time) {
        // Sesión expirada
        session_unset();
        session_destroy();
        header("Location: login.php"); // Redirigir a la página de inicio de sesión
        exit;
    } else {
        // Actualizar la marca de tiempo de la última actividad
        $_SESSION['last_activity'] = time();
    }
} else {
    // Sesión no iniciada
    header("Location: login.php"); // Redirigir a la página de inicio de sesión
    exit;
}

// Mostrar contenido de la página principal
echo "Bienvenido, " . $_SESSION['username'] . "!";
echo $_SESSION['last_activity'];

// Enlace para cerrar sesión
echo '<br><a href="cerrar_sesion.php">Cerrar sesión</a>';

?>