<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; padding-top: 50px; background-color: #f4f4f9; }
        .form-container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>¿Olvidaste tu contraseña?</h2>
    <p>Ingresa tu correo y te enviaremos una clave temporal.</p>
    
    <form action="procesar_recuperar.php" method="POST">
        <label for="email">Correo electrónico:</label>
        <input type="email" id="email" name="email" required placeholder="ejemplo@correo.com">
        <button type="submit">Enviar solicitud</button>
    </form>
    
    <a href="login.php" class="back-link">Volver al inicio de sesión</a>
</div>

</body>
</html>