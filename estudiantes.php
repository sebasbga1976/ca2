<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'conexion.php';
require_once 'cripto.php';

// Validar entrada
$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS);
if (!$token) {
    die("No se ha especificado un código de estudiante.");
}

$codpin = decryptToken($token);
if (!$codpin) {
    die("Token inválido.");
}

// Consultas
$sql_estudiante = "SELECT distinct e.Estcod, ca.Nombre_Programa, e.cohortecod, e.estest2cod, 
                          e.estindiceacad, e.estcondcod, e.estcredtotcursapro
                   FROM Cliente_Estudiante ce
                   INNER JOIN Estudiante e ON ce.EstCod = e.Estcod
                   INNER JOIN Carreras ca ON e.carrcod = ca.CarrCod
                   WHERE ce.Codpin = :codpin
                   ORDER BY e.Estcod";

$stmt = $pdo->prepare($sql_estudiante);
$stmt->execute([':codpin' => $codpin]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql_persona = "SELECT PNombre, SNombre, PApellido, SApellido FROM Cliente WHERE Codpin = :codpin";
$stmt_p = $pdo->prepare($sql_persona);
$stmt_p->execute([':codpin' => $codpin]);
$persona = $stmt_p->fetch(PDO::FETCH_ASSOC);

if (!$persona) {
    die("Persona no encontrada.");
}

$nombre_completo = "{$persona['PNombre']} {$persona['SNombre']} {$persona['PApellido']} {$persona['SApellido']}";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de Estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="menu.php">Sistema Académico</a>
            <div class="ms-auto">
                <a href="salir.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 text-secondary mb-0">
                <i class="fas fa-user-graduate me-2"></i><?= htmlspecialchars($nombre_completo) ?>
            </h2>
            <a href="personas.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Volver</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Registros Académicos</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Programa</th>
                                <th>Código</th>
                                <th>Cohorte</th>
                                <th>Estado</th>
                                <th>Condición</th>
                                <th>Promedio</th>
                                <th>Créditos</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($registros): ?>
                                <?php foreach ($registros as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['Nombre_Programa']) ?></td>
                                        <td><?= htmlspecialchars($r['Estcod']) ?></td>
                                        <td><?= htmlspecialchars($r['cohortecod']) ?></td>
                                        <td><?= htmlspecialchars($r['estest2cod']) ?></td>
                                        <td><?= htmlspecialchars($r['estcondcod']) ?></td>
                                        <td><?= number_format((float)$r['estindiceacad'], 2) ?></td>
                                        <td><?= htmlspecialchars($r['estcredtotcursapro'] ?? 'N/A') ?></td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">

                                                <a href="historial.php?token=<?= encryptToken(urlencode($r['Estcod'])) ?>" class="btn btn-sm btn-outline-info" title="Ver Historial">
                                                    <i class="fas fa-book"></i>
                                                </a>
                                                
                                                <a href="pdf.php?token=<?= encryptToken(urlencode($r['Estcod'])) ?>" class="btn btn-sm btn-outline-danger" title="Descargar PDF">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>

                                                <a href="notas.php?token=<?= encryptToken(urlencode($r['Estcod'])) ?>" class="btn btn-sm btn-outline-primary" title="Notas">
                                                    <i class="fa fa-commenting" aria-hidden="true"></i>
                                                </a>

                                                <a href="fp.php?token=<?= encryptToken(urlencode($r['Estcod'])) ?>" class="btn btn-sm btn-outline-primary" title="Formación permanente">
                                                    <i class="fa fa-commenting" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center py-3">No hay registros asociados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>