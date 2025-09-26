<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once "../conexion.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_REQUEST['email'];
    $contrasena = $_REQUEST['password'];
    print $contrasena;
}
// Cerrar conexión al final

?>