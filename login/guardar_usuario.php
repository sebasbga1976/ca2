<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include_once '../conexion.php';

    $Nombres   = strtoupper(trim($_POST['nombres'] ?? ''));
    $Apellidos = strtoupper(trim($_POST['apellidos'] ?? ''));
    $Correo    = strtolower(trim($_POST['email'] ?? ''));
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    
    // 1. Validar contraseñas
    if ($password !== $password2) {
        echo "❌ Las contraseñas no coinciden. <a href='register.php'>Volver</a>";        
        exit;
    }

    // 2. Verificar si ya existe el usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $Correo]);
        
    if ($stmt->fetch()) {
        echo "❌ El correo ya está registrado. <a href='register.php'>Volver</a>";
        exit;
    }

   // 3. Encriptar y ejecutar inserción
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (Nombres, Apellidos, Email, password) 
            VALUES (:nombres, :apellidos, :email, :password)";
    
    $stmt = $pdo->prepare($sql);

    try {
        // Ejecutamos la consulta
        $stmt->execute([
            ':nombres'   => $Nombres,
            ':apellidos' => $Apellidos,
            ':email'    => $Correo,
            ':password' => $password_hash
        ]);

        // SI LLEGA AQUÍ, TODO SALIÓ BIEN. REDIRIGIMOS.
        header("Location: ../index.php");
        exit(); // IMPORTANTE: Terminar el script tras el header

    } catch (PDOException $e) {
        // Si hay error, el código salta aquí
        die("❌ Error en la base de datos: " . $e->getMessage());
    }

} else {
    // Esto se ejecutará cuando entres a la página normalmente (GET)
    echo "<h1>El archivo está cargando correctamente.</h1>";
    echo "<p>Esperando datos del formulario...</p>";
}



?>