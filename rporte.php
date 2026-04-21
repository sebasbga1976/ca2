<?php

declare(strict_types=1);
$Nombre = "";
$Documento ="";
$Programa = "";
$Expedido = "";

// Habilitar la visualización de errores para depuración (solo en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Asegúrate de que estos archivos existan en el mismo directorio
require_once "conexion.php";
require_once "numletras.php";

// -------------------- Conexión a la base de datos --------------------
try {

// La función 'conectarBD()' debe estar definida en 'conexion.php'

// $pdo = conectarBD();

} catch (PDOException $e) {

die("Error al conectar a la base de datos: " . $e->getMessage());

}
// -------------------- Obtención del código de estudiante --------------------
// Se espera el código en el parámetro 'estcod' de la URL
$Codigo = $_REQUEST['estcod'] ?? null;

if ($Codigo === null) {
    die("Error: El código de estudiante no fue proporcionado.");
}

// -------------------- Consulta SQL con sintaxis PDO --------------------
$sql = "
SELECT h.Perano, h.Persecuencia, h.Matcod, m.matnombre AS Mat_Nombre, h.Condcod,
    ROUND(h.Matcursadascalif, 1) AS Matcursadascalif, h.Matcursadascaracter,
    h.Matcreditos, h.Matcarrhorteoricas, h.Matcarrhorpracticas, h.Calificacionesestatus,
    ROUND((h.Matcreditos * h.Matcursadascalif), 1) AS producto, h.Carrlapsonro,
    (h.Matcarrhorteoricas + h.Matcarrhorpracticas) as horas, p.PromedioSemestre, p.PromedioAcumulado   
    FROM Historial h
    INNER JOIN Materias m ON m.MatCod = h.Matcod
    INNER join promediosestudiantes p on p.Persecuencia = h.Persecuencia AND p.Estcod =h.Estcod
    WHERE h.Estcod = ?
    ORDER BY h.Persecuencia, m.matnombre
";

$DatosEstudiante ="
    select DISTINCT  
    upper(concat(c.PNombre,' ',c.SNombre,' ',c.PApellido,' ',c.SApellido)) as Nombre, 
    c.Codpin, upper(c.`Exp`) as Exp,  ce.CodEst, upper(c2.Nombre_Programa) as Nombre_Programa   
    from Cliente c
    inner join cli_est ce on c.Codpin = ce.Codpin  
    INNER join Estudiante e  on e.Estcod = ce.CodEst 
    INNER join Carreras c2 on e.carrcod = c2.Cod_Programa 
    WHERE ce.CodEst = ?
";

// -------------------- Preparación y ejecución de la consulta con PDO --------------------
$stmt = $pdo->prepare($sql);
if (!$stmt) {
    die("Problemas en la preparación de la consulta: " . implode(" ", $pdo->errorInfo()));
}

$stmt->execute([$Codigo]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt2 = $pdo->prepare($DatosEstudiante);
if (!$stmt2) {
    die("Problemas en la preparación de la consulta: " . implode(" ", $pdo->errorInfo()));
}

$stmt2->execute([$Codigo]);
$registros2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

foreach ($registros2 as $dat) {
    $Nombre = $dat['Nombre'];
    $Documento = $dat['Codpin'];
    $Programa = $dat['Nombre_Programa'];
    $Expedido = $dat['Exp'];    
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Certificado</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <?php    
    $current_period = null;
    include "fecha.php";
    $fechaactual=date("dmY");
    ?>
        <div>
            <p style="width: 1100;">
                Bucaramanga, <?php  echo f_actual($fechaactual); ?> <br>
            </p>
        </div>    

        <div class="text-center">            
            <p style="width: 1100;">EL SECRETARIO GENERAL DE LA<br>UNIVERSIDAD DE SANTANDER.<br>NIT 804.001.890-1</p><br>
            <p style="width: 1100;"><strong>CERTIFICA</strong></p><br>
        </div>
        <div class="text-justy">
            <P style="width: 1100px;">Que <?php echo $Nombre;  ?> con documento de identificación No. <?php echo $Documento;  ?>
            de <?php echo $Expedido;  ?> y código de estudiante <?php echo "".$Codigo;  ?>
            obtuvo las siguientes calificaciones en el programa <?php echo $Programa;  ?>.</p>
        </div>
    <?php

    $totalRegistros = count($registros);
    $contador = 0;

    foreach ($registros as $reg) {
        $contador++;

        // Inicia una nueva tabla para cada periodo diferente
        if ($reg['Persecuencia'] !== $current_period) {
            // Antes de cerrar la tabla del período anterior, imprime los promedios
            if ($current_period !== null) {
                echo "<tr><td colspan='4' class='info text-right'><strong>Promedio Semestre: {$promedioSemestre} " . numletras($promedioSemestre) . "</strong></td></tr>";
                echo "<tr><td colspan='4' class='info text-right'><strong>Promedio Acumulado: {$promedioAcumulado} " . numletras($promedioAcumulado) . "</strong></td></tr>";                
                echo '</tbody></table></div>';
            }
            $current_period = $reg['Persecuencia'];
            echo "<h3><strong>Periodo: " . substr($reg['Perano'], 0, 2) . "{$current_period}</strong></h3>";
            echo '<div class="table-responsive">';
            echo '<table class="table">
                    <thead>
                        <tr class="info">
                            <th>ASIGNATURA CURSADA</th>
                            <th>CALIFICACIÓN</th>
                            <th>CRÉDITOS</th>
                            <th>HORAS/SEM.</th>
                        </tr>
                    </thead>
                <tbody>';
        }

        // Guarda los promedios del registro actual para usarlos al final del período
        $promedioSemestre = $reg['PromedioSemestre'];
        $promedioAcumulado = $reg['PromedioAcumulado'];

        // Determina la clase CSS para la fila
        $condicion = ($reg['Condcod'] === 'S') ? "success" : "danger";

        echo "<tr class='{$condicion}'>";
        echo "<td>{$reg['Mat_Nombre']}</td>";
        // Muestra la calificación
        if ($reg['Carrlapsonro'] === "BIEN" || ($reg['Carrlapsonro'] === "INF" && $reg['Matcursadascaracter'] !== '')) {
            echo "<td>{$reg['Matcursadascaracter']}</td>";
        } else {
            echo "<td>{$reg['Matcursadascalif']} " . numletras($reg['Matcursadascalif']) . "</td>";
        }

        echo "<td>{$reg['Matcreditos']}</td>";
        echo "<td>{$reg['horas']}</td>";        
        echo "</tr>";

        // Si es el último registro del bucle, imprime los promedios
        if ($contador === $totalRegistros) {
            echo "<tr><td colspan='4' class='info text-right'><strong>Promedio Semestre: {$promedioSemestre} " . numletras($promedioSemestre) . "</strong></td></tr>";
            echo "<tr><td colspan='4' class='info text-right'><strong>Promedio Acumulado: {$promedioAcumulado} " . numletras($promedioAcumulado) . "</strong></td></tr>";
        }
    }

    // Cierra la última tabla si se procesó al menos un registro
    if ($current_period !== null) {
        echo '</tbody></table></div>';
    }
    ?>
</div>
</body>
</html>