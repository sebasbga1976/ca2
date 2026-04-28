<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'conexion.php';
require_once 'cripto.php';

// Validar y descifrar token
$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS);
if (!$token) die("No se ha especificado un código de estudiante.");

$estcod = decryptToken($token);
if (!$estcod) die("Token inválido.");

// Consulta actualizada con Matcod
$stmt = $pdo->prepare("
    SELECT h.Id_Historico, h.Matcod, h.Perano, h.Persecuencia, h.Carrlapsonro, m.Mat_Nombre, 
    h.Matcursadasescnumerica, h.Matcursadascaracter, h.Matcursadascalif 
    FROM Historial h 
    INNER JOIN Materias m ON h.Matcod = m.Mat_Cod 
    WHERE h.Carrlapsonro IN ('IDIO', 'INF', 'BIEN') 
    AND h.estcod = :estcod
    ORDER BY Perano asc, Persecuencia asc
");
$stmt->execute(['estcod' => $estcod]);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

function esAprobado($fila) {
    if ((int)$fila['Matcursadasescnumerica'] === 1) {
        return (float)$fila['Matcursadascalif'] >= 3.0;
    } else {
        return trim($fila['Matcursadascaracter']) === 'A';
    }
}

// Clasificar resultados
$tablas = [
    'IDIO' => ['titulo' => 'Idiomas', 'datos' => []],
    'INF'  => ['titulo' => 'Informática', 'datos' => []],
    'BIEN' => ['titulo' => 'Bienestar', 'datos' => []]
];

foreach ($resultados as $fila) {
    if (isset($tablas[$fila['Carrlapsonro']])) {
        $tablas[$fila['Carrlapsonro']]['datos'][] = $fila;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Académico</title>
    <style>
        :root { --primary: #2563eb; --success: #16a34a; --bg: #f8fafc; --text: #334155; }
        body { font-family: system-ui, sans-serif; background-color: var(--bg); color: var(--text); padding: 20px; line-height: 1.5; }
        .container { max-width: 900px; margin: 0 auto; }
        
        .card { background: white; border-radius: 12px; padding: 20px; margin-bottom: 30px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        
        /* Stats Dashboard */
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat-item { flex: 1; text-align: center; padding: 15px; border-radius: 8px; background: #f1f5f9; }
        .stat-val { display: block; font-size: 1.5rem; font-weight: bold; color: var(--primary); }

        /* Tables */
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f1f5f9; padding: 12px; text-align: left; border-bottom: 2px solid #e2e8f0; }
        td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        
        /* Link styles */
        .btn-link { color: #2563eb; text-decoration: none; font-weight: 600; font-size: 0.9em; }
        .btn-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="container">
        <h1>Historial cursos formación permanente</h1>

        <?php foreach ($tablas as $key => $tabla): 
            $datos = $tabla['datos'];
            $total = count($datos);
            $aprobados = count(array_filter($datos, 'esAprobado'));
        ?>
            <section class="card">
                <h2><?= htmlspecialchars($tabla['titulo']) ?></h2>
                
                <div class="stats">
                    <div class="stat-item">Total Cursos <span class="stat-val"><?= $total ?></span></div>
                    <div class="stat-item" style="color:var(--success)">Aprobados <span class="stat-val"><?= $aprobados ?></span></div>                
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Año</th>
                                <th>Sec</th>
                                <th>Materia</th>
                                <th>Nota</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($datos as $fila): 
                                $esAprobado = esAprobado($fila);
                                $nota = ($fila['Matcursadasescnumerica'] == 1) ? $fila['Matcursadascalif'] : $fila['Matcursadascaracter'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($fila['Perano']) ?></td>
                                    <td><?= htmlspecialchars($fila['Persecuencia']) ?></td>
                                    <td><?= htmlspecialchars($fila['Mat_Nombre']) ?></td>
                                    <td style="font-weight:bold; font-size: 1.1em; color: #1e293b;">
                                        <?= htmlspecialchars($nota) ?>
                                    </td>
                                    <td>
                                        <?php if ($key === 'INF' && $esAprobado): ?>                                            
                                            <a href="certificado.php?token=<?= encryptToken(($fila['Id_Historico'])) ?>" 
                                               class="btn-link">
                                               Certificado
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #cbd5e1;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

</body>
</html>