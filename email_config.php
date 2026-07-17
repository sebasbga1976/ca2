<?php
// email_config.php

// 1. Declaramos que queremos usar la variable $pdo del ámbito global
global $pdo, $db;

// 2. Si por alguna razón no se ha incluido la conexión aún, la cargamos
if (!isset($pdo) && !isset($db)) {
    require_once 'conexion.php'; 
    // Volvemos a declarar global por si la carga ocurrió dentro de este require
    global $pdo, $db;
}

// 3. Si tu sistema usa $db en lugar de $pdo, hacemos la equivalencia
if (!isset($pdo) && isset($db)) {
    $pdo = $db;
}

// 4. Verificación de seguridad: si de plano sigue vacía, lanzamos una excepción limpia
if (!$pdo) {
    throw new \Exception("La variable de conexión (\$pdo) no está disponible en el ámbito global.");
}

try {
    // 5. Consultamos los parámetros de configuración
    $stmt = $pdo->query("SELECT clave, valor FROM settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Devuelve ['clave' => 'valor']

    // 6. Retornamos la configuración
    return [
        'smtp_host'  => $settings['smtp_host'] ?? 'smtp.gmail.com',
        'smtp_user'  => $settings['smtp_user'] ?? '',
        'smtp_pass'  => $settings['smtp_pass'] ?? '', 
        'smtp_port'  => (int)($settings['smtp_port'] ?? 587),
        'from_email' => $settings['from_email'] ?? '',
        'from_name'  => $settings['from_name'] ?? 'Sistema Académico'
    ];

} catch (\Exception $e) {
    error_log("Error al cargar la configuración de correo: " . $e->getMessage());
    
    // Retornamos un plan de respaldo (fallback) para evitar que la aplicación muera
    return [
        'smtp_host'  => 'smtp.gmail.com',
        'smtp_user'  => '',
        'smtp_pass'  => '',
        'smtp_port'  => 587,
        'from_email' => '',
        'from_name'  => 'Sistema Académico'
    ];
}