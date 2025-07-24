<?php
declare(strict_types=1); // Habilita el modo estricto de tipos

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once 'conexion.php';

// Verificar si se recibió el Codpin por GET
if (!isset($_GET['codpin']) || empty($_GET['codpin'])) {
    die("No se ha especificado un Codpin de persona.");
}

$codpin = $_GET['codpin'];

// Consulta SQL para obtener todos los registros de estudiante para el Codpin especificado
$sql = "SELECT distinct
c2.Nombre_Programa AS ProgramaEstudiante,
e.cohortecod AS CohorteEstudiante,
e.Estcod AS CodigoEstudiante,
e.estest2cod as EstadoEstudiante,
e.estindiceacad AS PromedioEstudiante,
e.estcondcod AS CondicionEstudiante,
e.estcredsolaprob AS MencionEstudiante
FROM cli_est c 
inner JOIN Estudiante e on c.CodEst = e.Estcod
INNER join Carreras c2 on e.carrcod = c2.Cod_Programa
WHERE c.Codpin = :codpin
ORDER BY c2.Nombre_Programa";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':codpin', $codpin, PDO::PARAM_STR);
$stmt->execute();
$registros_estudiante = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta SQL para obtener la información básica de la persona
$sql_persona = "SELECT PNombre, SNombre, PApellido, SApellido FROM Cliente WHERE Codpin = :codpin";
$stmt_persona = $pdo->prepare($sql_persona);
$stmt_persona->bindParam(':codpin', $codpin, PDO::PARAM_STR);
$stmt_persona->execute();
$persona = $stmt_persona->fetch(PDO::FETCH_ASSOC);

if (!$persona) {
    die("No se encontró información de la persona para el Codpin: " . htmlspecialchars($codpin));
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de Estudiante</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: sans-serif;
        }
        .student-records {
            margin-top: 20px;
        }
        th, td {
            text-align: left;
        }
        .view-history-btn {
            padding: 6px 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9em;
        }
        .view-history-btn:hover {
            background-color: #1e7e34;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registros de Estudiante</h1>
        <h2><?php echo htmlspecialchars($persona['PNombre'] . ' ' . ($persona['SNombre'] ?? '') . ' ' . $persona['PApellido'] . ' ' . ($persona['SApellido'] ?? '')); ?></h2>

        <?php if (!empty($registros_estudiante)): ?>
            <table class="table student-records">
                <thead>
                    <tr>
                        <th>Programa Académico</th>
                        <th>Código Estudiante</th>
                        <th>Cohorte</th>
                        <th>Estado Estudiante</th>
                        <th>Condición Estudiante</th>
                        <th>Promedio</th>
                        <th>Créditos aprobados</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros_estudiante as $registro): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($registro['ProgramaEstudiante']); ?></td>
                            <td><?php echo htmlspecialchars($registro['CodigoEstudiante']); ?></td>
                            <td><?php echo htmlspecialchars($registro['CohorteEstudiante']); ?></td>
                            <td><?php echo htmlspecialchars($registro['EstadoEstudiante']); ?></td>
                            <td><?php echo htmlspecialchars($registro['CondicionEstudiante']); ?></td>
                            <td><?php echo htmlspecialchars(number_format((float)$registro['PromedioEstudiante'], 2, '.', '')); ?></td>
                            <td><?php echo htmlspecialchars($registro['MencionEstudiante'] ?? 'N/A'); ?></td>
                            <td>
                                <a href="historial.php?estcod=<?php echo urlencode($registro['CodigoEstudiante']); ?>" class="view-history-btn">Ver Historial</a>
                                <a href="pdf.php?estcod=<?php echo urlencode($registro['CodigoEstudiante']); ?>" class="view-pdf-btn">pdf</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No se encontraron registros de estudiante para esta persona.</p>
        <?php endif; ?>

        <p><a href="personas.php" class="btn btn-primary">Volver a la lista de Personas</a></p>
    </div>
</body>
</html>