<?php
    session_start();
    include_once "../conexion.php";
    
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

         $sql = "SELECT id, Nombres, Apellidos, password AS password_hash 
         FROM usuarios
         WHERE Email = :email";    
         $stmt = $pdo->prepare($sql);
         $stmt->execute([':email' => $email]);         
         $fila = $stmt->fetch(PDO::FETCH_ASSOC);
         if ($fila && password_verify($password, $fila['password_hash'])) {
             $_SESSION['autenticado'] = true;
             $_SESSION['id_usuario'] = $fila['id_usuario'];
             $_SESSION['nombre_completo'] = $fila['nombre']." ".$fila['apellido'];

             header("Location: ../menu.php");
             exit;
         } else {
             $_SESSION['error'] = "Credenciales incorrectas";
             header("Location: ../index.php");
             exit;
         }
    } 
     else {
         header("Location: ../index.php");
         exit;
    } 
?>