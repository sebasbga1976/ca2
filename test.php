<?php
require_once 'conexion.php';

if (isset($pdo)) {
    echo "Conexión a la base de datos exitosa.";
} else {
    echo "Error: La variable \$pdo no está definida. Revisa conexion.php.";
}
?>