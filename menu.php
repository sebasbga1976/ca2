<?php
// 1. TEMPORAL: Mostrar errores en pantalla si algo falla (evita la pantalla en blanco)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'conexion.php';
require_once 'cripto.php';

// Variables de paginación
$registros_por_pagina = 10;
$pagina_actual = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?: 1;
$inicio = ($pagina_actual - 1) * $registros_por_pagina;

// Filtros simplificados
$filtros = [];
$condiciones = ["1=1"];

$mapeo_campos = [
    'EstCod' => 'ce.EstCod',
    'Documento' => 'c.Codpin',
    'primer_nombre' => 'c.PNombre',
    'segundo_nombre' => 'c.SNombre',
    'primer_apellido' => 'c.PApellido',
    'segundo_apellido' => 'c.SApellido'
];

foreach ($mapeo_campos as $get_key => $db_col) {
    if (!empty($_GET[$get_key])) {
        $condiciones[] = "$db_col LIKE :$get_key";
        $filtros[":$get_key"] = '%' . trim($_GET[$get_key]) . '%';
    }
}

// Filtros exactos (Selects)
$es_estudiante = $_GET['es_estudiante'] ?? '';
$es_docente = $_GET['es_docente'] ?? '';

if ($es_estudiante !== '') {
    $condiciones[] = "c.Estudiante = :es_estudiante";
    $filtros[':es_estudiante'] = $es_estudiante;
}
if ($es_docente !== '') {
    $condiciones[] = "c.Docente = :es_docente";
    $filtros[':es_docente'] = $es_docente;
}

$where_sql = implode(" AND ", $condiciones);

// Consulta principal (Limpia de caracteres invisibles)
$sql_select = "SELECT DISTINCT c.Codpin, UPPER(c.PNombre) as PNombre, UPPER(c.SNombre) as SNombre, 
               UPPER(c.PApellido) as PApellido, UPPER(c.SApellido) as SApellido 
               FROM Cliente c 
               INNER JOIN Cliente_Estudiante ce ON c.Codpin = ce.Codpin
               INNER JOIN Estudiante e ON ce.EstCod = e.Est_Cod
               WHERE $where_sql 
               ORDER BY c.PApellido, c.SApellido 
               LIMIT :inicio, :registros";

$stmt = $pdo->prepare($sql_select);
foreach ($filtros as $clave => $valor) {
    $stmt->bindValue($clave, $valor);
}
$stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
$stmt->bindValue(':registros', $registros_por_pagina, PDO::PARAM_INT);
$stmt->execute();
$personas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total para paginación (Limpia de caracteres invisibles)
$sql_total = "SELECT COUNT(DISTINCT c.Codpin) FROM Cliente c 
              INNER JOIN Cliente_Estudiante ce ON c.Codpin = ce.Codpin
              INNER JOIN Estudiante e ON ce.EstCod = e.Est_Cod 
              WHERE $where_sql";
              
$stmt_total = $pdo->prepare($sql_total);
foreach ($filtros as $clave => $valor) {
    $stmt_total->bindValue($clave, $valor);
}
$stmt_total->execute();

$total_registros = (int) $stmt_total->fetchColumn();
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Calculamos los números para el texto "Mostrando X a Y de Z"
$inicio_mostrado = ($total_registros > 0) ? ($inicio + 1) : 0;
$fin_mostrado = min($inicio + count($personas), $total_registros);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Personas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Sistema Académico</a>
            <div class="ms-auto">
                <a href="salir.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span>Filtrar Búsqueda</span>
                <?php if (!empty(array_filter($_GET))): ?>
                    <a href="?" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.8rem;">
                        <i class="fas fa-undo"></i> Limpiar Filtros
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form method="get" class="row g-3">
                    <?php foreach ($mapeo_campos as $key => $col): ?>
                        <div class="col-md-3">
                            <input class="form-control form-control-sm" name="<?= $key ?>" placeholder="<?= ucwords(str_replace('_', ' ', $key)) ?>" value="<?= htmlspecialchars($_GET[$key] ?? '') ?>">
                        </div>
                    <?php endforeach; ?>
                    <div class="col-md-2">
                        <select name="es_estudiante" class="form-select form-select-sm">
                            <option value="">¿Es Estudiante?</option>
                            <option value="1" <?= $es_estudiante === '1' ? 'selected' : '' ?>>Sí</option>
                            <option value="0" <?= $es_estudiante === '0' ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i> Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Documento</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($personas) > 0): ?>
                                <?php foreach ($personas as $p): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p['Codpin']) ?></td>
                                        <td><?= htmlspecialchars($p['PNombre'] . ' ' . $p['SNombre']) ?></td>
                                        <td><?= htmlspecialchars($p['PApellido'] . ' ' . $p['SApellido']) ?></td>
                                        <td class="text-center">
                                            <!-- Token encriptado y codificado correctamente para la URL -->
                                            <a href="estudiantes.php?token=<?= urlencode(encryptToken($p['Codpin'])) ?>" class="btn btn-sm btn-outline-primary" title="Ver Perfil">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                        No se encontraron resultados que coincidan con la búsqueda.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-center my-3">
            <div class="alert alert-info py-2 mb-3">
                <?php if ($total_registros > 0): ?>
                    Mostrando <strong><?= $inicio_mostrado ?></strong> a <strong><?= $fin_mostrado ?></strong> de <strong><?= $total_registros ?></strong> resultados.
                <?php else: ?>
                    No se encontraron registros.
                <?php endif; ?>
            </div>
        </div>

        <?php if ($total_paginas > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $pagina_actual <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $pagina_actual - 1 ?>&<?= http_build_query(array_diff_key($_GET, ['pagina' => ''])) ?>">Anterior</a>
                    </li>
                    <li class="page-item active"><span class="page-link"><?= $pagina_actual ?></span></li>
                    <li class="page-item <?= $pagina_actual >= $total_paginas ? 'disabled' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $pagina_actual + 1 ?>&<?= http_build_query(array_diff_key($_GET, ['pagina' => ''])) ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
    
</body>
</html>