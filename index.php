<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <style>
        body {
            font-family: sans-serif;
            background: linear-gradient(135deg, #E0F2FE, #81D4FA, #29B6F6);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .login-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }

        .logo {
            max-width: 500px;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .input-group input {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .login-button {
            background-color: #29B6F6;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        .login-button:hover {
            background-color: #0288D1;
        }

        .forgot-password {
            margin-top: 20px;
        }

        .forgot-password a {
            color: #29B6F6;
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>

<div class="login-container">
    <img src="../img/logo.png" alt="Logo Institucional" class="logo">
    <h2>Inicio de Sesión</h2>

    <form action="procesar_login.php" method="post">
        <div class="input-group">
            <label for="username">Usuario</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div class="input-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="login-button">Iniciar Sesión</button>
    </form>

    <div class="forgot-password">
        <a href="restablecer_contrasena.php">¿Olvidaste tu contraseña?</a>
    </div>
</div>

</body>
</html>