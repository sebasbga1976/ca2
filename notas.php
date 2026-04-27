<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'conexion.php';
require_once 'cripto.php';

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS);
if (!$token) die("No se ha especificado un código de estudiante.");

$codpin = decryptToken($token);
if (!$codpin) die("Token inválido.");

// Preparación segura
// 1. Usamos :codpin para que coincida con el array
$stmt = $pdo->prepare("SELECT codpin, estcod, Fecha, tipo, titulo, nota 
                       FROM newnotas 
                       WHERE estcod = :codpin 
                       ORDER BY Fecha ASC");
$stmt->execute(['codpin' => $codpin]);
$notas = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtenemos todo de una vez
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notas del Estudiante</title>
    <style>
        table { width: 100%; border-collapse: collapse; font-family: sans-serif; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #333; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .no-data { padding: 20px; text-align: center; color: #666; font-style: italic; }
    </style>
</head>
<body>

    <h2>Notas del Estudiante</h2>

    <?php if (count($notas) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Título</th>
                    <th>Nota</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notas as $row): ?>
                    <tr>
                        <td class="fecha"><?php echo htmlspecialchars($row['Fecha']); ?></td>
                        <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['nota'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">No hay notas registradas para este estudiante.</p>
    <?php endif; ?>

</body>
</html>