<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de Usuario</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card shadow-lg border-0 rounded-4">
          <div class="card-body p-4">
            <h3 class="text-center mb-4">Crear cuenta</h3>
            
            <form method="post" action="guardar_usuario.php" class="needs-validation" novalidate>
              <!-- Nombre -->
              <div class="mb-3">
                <label for="nombres" class="form-label">Nombres</label>
                <input type="text" class="form-control" id="nombres" name="nombres" required>
                <div class="invalid-feedback">Por favor ingresa tu nombre.</div>
              </div>

              <!-- Apellido -->
              <div class="mb-3">
                <label for="apellidos" class="form-label">Apellidos</label>
                <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                <div class="invalid-feedback">Por favor ingresa tu apellido.</div>
              </div>

              <!-- Correo -->
              <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div class="invalid-feedback">Ingresa un correo válido.</div>
              </div>

              <!-- Contraseña -->
              <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                <div id="passwordHelp" class="form-text text-muted">
                  Debe tener al menos 6 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales.
                </div>
                <div class="invalid-feedback">La contraseña no cumple con los requisitos.</div>
              </div>

              <div class="mb-3">
                <label for="password2" class="form-label">Confirmar Contraseña</label>
                <input type="password" class="form-control" id="password2" name="password2" required minlength="6">
                <div class="invalid-feedback">Las contraseñas no coinciden.</div>
              </div>

              <!-- Botón -->
              <div class="d-grid">
                <button type="submit" id="btnRegistrar" class="btn btn-primary btn-lg" disabled>Registrar</button>
              </div>
            </form>

            <hr class="my-4">

            <div class="text-center">
              <small>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    const password = document.getElementById('password');
    const password2 = document.getElementById('password2');
    const btnRegistrar = document.getElementById('btnRegistrar');

    function validarPassword() {
      const valor = password.value;
      const confirmacion = password2.value;

      // Expresión regular: minúscula, mayúscula, número, especial, mínimo 6
      const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/;

      const cumpleFormato = regex.test(valor);
      const coincide = valor === confirmacion && valor.length > 0;

      if (cumpleFormato && coincide) {
        btnRegistrar.disabled = false;
        password.classList.remove("is-invalid");
        password2.classList.remove("is-invalid");
      } else {
        btnRegistrar.disabled = true;
        if (!cumpleFormato) password.classList.add("is-invalid");
        else password.classList.remove("is-invalid");

        if (!coincide) password2.classList.add("is-invalid");
        else password2.classList.remove("is-invalid");
      }
    }

    password.addEventListener('input', validarPassword);
    password2.addEventListener('input', validarPassword);
  </script>
</body>
</html>
