<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once 'conexion.php';

$registros_por_pagina = 10;
$pagina_actual = (isset($_GET['pagina']) && is_numeric($_GET['pagina'])) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina_actual - 1) * $registros_por_pagina;

// ==== Filtros para búsqueda y paginación ====
$sql_base = "FROM Cliente c
             INNER JOIN cli_est ce ON c.Codpin = ce.Codpin
             WHERE 1 = 1";

$filtros = [];
if (!empty($_GET['Documento'])) {
    $sql_base .= " AND c.Codpin LIKE :Documento";
    $filtros[':Documento'] = '%' . $_GET['Documento'] . '%';
}
if (!empty($_GET['primer_nombre'])) {
    $sql_base .= " AND c.PNombre LIKE :primer_nombre";
    $filtros[':primer_nombre'] = '%' . $_GET['primer_nombre'] . '%';
}
if (!empty($_GET['segundo_nombre'])) {
    $sql_base .= " AND c.SNombre LIKE :segundo_nombre";
    $filtros[':segundo_nombre'] = '%' . $_GET['segundo_nombre'] . '%';
}
if (!empty($_GET['primer_apellido'])) {
    $sql_base .= " AND c.PApellido LIKE :primer_apellido";
    $filtros[':primer_apellido'] = '%' . $_GET['primer_apellido'] . '%';
}
if (!empty($_GET['segundo_apellido'])) {
    $sql_base .= " AND c.SApellido LIKE :segundo_apellido";
    $filtros[':segundo_apellido'] = '%' . $_GET['segundo_apellido'] . '%';
}
if (isset($_GET['es_estudiante']) && $_GET['es_estudiante'] !== '') {
    $sql_base .= " AND c.Estudiante = :es_estudiante";
    $filtros[':es_estudiante'] = $_GET['es_estudiante'];
}
if (isset($_GET['es_docente']) && $_GET['es_docente'] !== '') {
    $sql_base .= " AND c.Docente = :es_docente";
    $filtros[':es_docente'] = $_GET['es_docente'];
}

// ==== Consulta con LIMIT ====
$sql_limit = "SELECT DISTINCT c.Codpin, 
                     UPPER(c.PNombre) AS PNombre,
                     UPPER(c.SNombre) AS SNombre,
                     UPPER(c.PApellido) AS PApellido,
                     UPPER(c.SApellido) AS SApellido
              $sql_base
              LIMIT :inicio, :registros";

$stmt = $pdo->prepare($sql_limit);
foreach ($filtros as $clave => $valor) {
    $stmt->bindValue($clave, $valor);
}
$stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
$stmt->bindValue(':registros', $registros_por_pagina, PDO::PARAM_INT);
$stmt->execute();
$personas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==== Conteo total para paginación ====
$sql_total = "SELECT COUNT(*) $sql_base";
$stmt_total = $pdo->prepare($sql_total);
foreach ($filtros as $clave => $valor) {
    $stmt_total->bindValue($clave, $valor);
}
$stmt_total->execute();
$total_registros = $stmt_total->fetchColumn();
$total_paginas = (int) ceil($total_registros / $registros_por_pagina);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Información de Personas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .pagination-custom {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 20px;
        }
        .pagination-custom a,
        .pagination-custom span {
            min-width: 100px;
            text-align: center;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
        }
        .pagination-custom .current {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        table {
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ccc;
        }
        .view-student-btn {
            background-color: #007bff;
            color: white;
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
        }
        .view-student-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body class="container">
    <h1 class="mt-4">Información de Personas</h1>

    <!-- Filtros -->
    <form method="get" class="mt-3 border p-3">
        <div class="row">
            <div class="col-md-2"><input class="form-control" name="Documento" placeholder="Número Documento" value="<?= htmlspecialchars($_GET['Documento'] ?? '') ?>"></div>
            <div class="col-md-3"><input class="form-control" name="primer_nombre" placeholder="Primer Nombre" value="<?= htmlspecialchars($_GET['primer_nombre'] ?? '') ?>"></div>
            <div class="col-md-2"><input class="form-control" name="segundo_nombre" placeholder="Segundo Nombre" value="<?= htmlspecialchars($_GET['segundo_nombre'] ?? '') ?>"></div>
            <div class="col-md-3"><input class="form-control" name="primer_apellido" placeholder="Primer Apellido" value="<?= htmlspecialchars($_GET['primer_apellido'] ?? '') ?>"></div>
            <div class="col-md-2"><input class="form-control" name="segundo_apellido" placeholder="Segundo Apellido" value="<?= htmlspecialchars($_GET['segundo_apellido'] ?? '') ?>"></div>
        </div>        
        <div class="row mt-2">
            <div class="col-md-4">
                <select name="es_estudiante" class="form-control">
                    <option value="">¿Es Estudiante?</option>
                    <option value="1" <?= ($_GET['es_estudiante'] ?? '') === '1' ? 'selected' : '' ?>>Sí</option>
                    <option value="0" <?= ($_GET['es_estudiante'] ?? '') === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="col-md-4">
                <select name="es_docente" class="form-control">
                    <option value="">¿Es Docente?</option>
                    <option value="1" <?= ($_GET['es_docente'] ?? '') === '1' ? 'selected' : '' ?>>Sí</option>
                    <option value="0" <?= ($_GET['es_docente'] ?? '') === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="col-md-4 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary mr-2">Filtrar</button>
                <a href="index.php" class="btn btn-secondary">Limpiar</a>
            </div>
        </div>
    </form>

    <!-- Tabla -->
    <?php if (!empty($personas)): ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Primer Nombre</th>
                    <th>Segundo Nombre</th>
                    <th>Primer Apellido</th>
                    <th>Segundo Apellido</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($personas as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['Codpin']) ?></td>
                        <td><?= htmlspecialchars($p['PNombre']) ?></td>
                        <td><?= htmlspecialchars($p['SNombre']) ?></td>
                        <td><?= htmlspecialchars($p['PApellido']) ?></td>
                        <td><?= htmlspecialchars($p['SApellido']) ?></td>
                        <td><a href="estudiantes.php?codpin=<?= urlencode($p['Codpin']) ?>" class="view-student-btn">Ver Estudiante</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <?php if ($total_paginas > 1): ?>
            <div class="pagination-custom">
                <?php if ($pagina_actual > 1): ?>
                    <a class="btn btn-outline-primary" href="?pagina=<?= $pagina_actual - 1 . '&' . http_build_query(array_diff_key($_GET, ['pagina' => ''])) ?>">Anterior</a>
                <?php else: ?>
                    <span class="btn btn-outline-secondary disabled">Anterior</span>
                <?php endif; ?>

                <span class="current"><?= $pagina_actual ?></span>

                <?php if ($pagina_actual < $total_paginas): ?>
                    <a class="btn btn-outline-primary" href="?pagina=<?= $pagina_actual + 1 . '&' . http_build_query(array_diff_key($_GET, ['pagina' => ''])) ?>">Siguiente</a>
                <?php else: ?>
                    <span class="btn btn-outline-secondary disabled">Siguiente</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <p class="mt-4">No se encontraron personas con los criterios de búsqueda.</p>
    <?php endif; ?>
</body>
</html>