<?php
// Conexión a la base de datos (reemplaza con tus datos)
$servername = "localhost";
$username = "sergio";
$password = "$3b4$";
$dbname = "Ara_Ca2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
return $conn; // Importante: retorna la conexión
?>