<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once('tcpdf/tcpdf.php');
ob_start();

include "fecha.php";
include "conexion.php";
require 'numletras.php';

$estcod = $_REQUEST['estcod'] ?? die("No se ha especificado un código de estudiante.");
$fechaactual = date("dmY");

$query_info = "SELECT ce.CodEst as EstCod, CONCAT(c.PNombre, ' ', c.SNombre, ' ', c.PApellido, ' ', c.SApellido) AS Nombre, c.Codpin, c.Exp, car.Nombre_Programa 
FROM cli_est ce 
INNER JOIN Cliente c ON c.Codpin = ce.Codpin
INNER join Estudiante e on ce.CodEst = e.Estcod
INNER JOIN Carreras car on e.carrcod = car.Cod_Programa 
WHERE ce.CodEst= ?";
$stmt_info = $pdo->prepare($query_info);
$stmt_info->bindParam(1, $estcod, PDO::PARAM_STR);
$stmt_info->execute();
$info = $stmt_info->fetch(PDO::FETCH_ASSOC);

if (!$info) {
    die("No se encontró información para el código de estudiante: " . htmlspecialchars($estcod));
}

$docnum = $info['Codpin'];
$nombre = $info['Nombre'];
$exp = $info['Exp'];
$cPais = $info['EstCod'];
$nom_prog = $info['Nombre_Programa'] ?? 'N/A';

$registros_historial = [];
$registros_homologa = [];
$promedio_acumulado = 0.0;
$per_ano_acad_acumulado = '';
$periodo_actual_acumulado = '';

if (strlen($cPais) > 8) {
    $query_historial = "SELECT h.Perano, h.Persecuencia, h.Matcod, m.matnombre, h.Condcod, ROUND(h.Matcursadascalif, 1) AS Matcursadascalif,
                               h.Matcursadascaracter, h.Matcreditos, h.Matcarrhorteoricas, h.Matcarrhorpracticas, h.Calificacionesestatus
                        FROM Historial h
                        INNER JOIN Materias m ON m.Mat_Cod = h.Matcod
                        WHERE h.Estcod = ? AND h.Carrlapsonro IN ('INF', 'IDIO', 'BIEN')
                        ORDER BY h.Persecuencia, m.matnombre";
    $stmt_historial = $pdo->prepare($query_historial);
    $stmt_historial->bindParam(1, $cPais, PDO::PARAM_STR);
    $stmt_historial->execute();
    $registros_historial = $stmt_historial->fetchAll(PDO::FETCH_ASSOC);
} else {
    $query_historial = "SELECT h.Perano, h.Persecuencia, h.Matcod, m.matnombre, h.Condcod, ROUND(h.Matcursadascalif, 1) AS Matcursadascalif,
h.Matcursadascaracter, h.Matcreditos, h.Matcarrhorteoricas, h.Matcarrhorpracticas, h.Calificacionesestatus,
ROUND((h.Matcreditos * h.Matcursadascalif), 1) AS producto
                        FROM Historial h
                        INNER JOIN Materias m ON m.Matcod = h.Matcod
                        WHERE h.Estcod = ?
                        ORDER BY h.Persecuencia, m.matnombre";
    $stmt_historial = $pdo->prepare($query_historial);
    $stmt_historial->bindParam(1, $cPais, PDO::PARAM_STR);
    $stmt_historial->execute();
    $result_historial = $stmt_historial->fetchAll(PDO::FETCH_ASSOC);

    $prod_total = 0.0;
    $creditos_total = 0;
    $periodo_actual = '';
    $per_ano_acad = '';
    $registros_por_periodo = [];
    $prod_semestre = 0.0;
    $sum_cred_semestre = 0;

    $prod_total = 0.0;
$creditos_total = 0;
$periodo_actual = '';
$per_ano_acad = '';
$registros_por_periodo = [];
$prod_semestre = 0.0;
$sum_cred_semestre = 0;
$periodo_actual_key = '';

foreach ($result_historial as $reg) {
    $per_ano_acad = substr($reg['Perano'], 0, 2);
    $clave_periodo = $per_ano_acad . $reg['Persecuencia'];

    // Cambio de periodo
    if ($periodo_actual !== $reg['Persecuencia']) {
        if ($sum_cred_semestre > 0 && $periodo_actual_key !== '') {
            $registros_por_periodo[$periodo_actual_key]['promedio_semestre'] = round($prod_semestre / $sum_cred_semestre, 2);
            $registros_por_periodo[$periodo_actual_key]['promedio_acumulado'] = round($prod_total / $creditos_total, 2);
        }

        $periodo_actual = $reg['Persecuencia'];
        $periodo_actual_key = $clave_periodo;
        $registros_por_periodo[$periodo_actual_key]['materias'] = [];

        $prod_semestre = 0.0;
        $sum_cred_semestre = 0;
    }

    // Agregar materia
    $registros_por_periodo[$clave_periodo]['materias'][] = $reg;

    // Solo si es válida para promedio
    if ($reg['Calificacionesestatus'] !== 'AT' && $reg['Matcreditos'] > 0) {
        $prod_total += $reg['producto'];
        $creditos_total += $reg['Matcreditos'];
        $prod_semestre += $reg['producto'];
        $sum_cred_semestre += $reg['Matcreditos'];
    }
}

// Finalizar el último periodo
if ($sum_cred_semestre > 0 && $periodo_actual_key !== '') {
    $registros_por_periodo[$periodo_actual_key]['promedio_semestre'] = round($prod_semestre / $sum_cred_semestre, 2);
    $registros_por_periodo[$periodo_actual_key]['promedio_acumulado'] = round($prod_total / $creditos_total, 2);
}

$registros_historial = $registros_por_periodo;


    if ($creditos_total > 0) {
        $promedio_acumulado = round($prod_total / $creditos_total, 2);
    }
    if ($sum_cred_semestre > 0) {
        $registros_por_periodo[$per_ano_acad . $periodo_actual]['promedio_semestre'] = round($prod_semestre / $sum_cred_semestre, 2);
    }
    $registros_historial = $registros_por_periodo;

    $query_homologa = "SELECT h.Persecuencia, m.matnombre, h.Calificacionesestatus
                       FROM Historial h
                       INNER JOIN Materias m ON m.matcod = h.Matcod
                       WHERE h.Estcod = ? AND h.Calificacionesestatus LIKE 'AT%'
                       ORDER BY m.matnombre ASC";
    $stmt_homologa = $pdo->prepare($query_homologa);
    $stmt_homologa->bindParam(1, $cPais, PDO::PARAM_STR);
    $stmt_homologa->execute();
    $registros_homologa = $stmt_homologa->fetchAll(PDO::FETCH_ASSOC);
}

class MYPDF extends TCPDF {
    // Footer personalizado
    public function Footer() {
        // Posición a 15 mm del final
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        // Número de página alineado a la derecha
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'R');
    }
}


// === TCPDF Config ===
$pdf = new MYPDF('P', 'mm', 'Letter', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Universidad de Santander');
$pdf->SetTitle('Historial Académico');
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();

// === Header HTML ===
$html_header = "<div style='text-align: center;'>
    <h2>UNIVERSIDAD DE SANTANDER</h2>
    <p>NIT 804.001.890-1</p>
    <h3>CERTIFICADO DE HISTORIAL ACADÉMICO</h3>
</div>
<p style='text-align: justify;'>Se certifica que el estudiante <strong>{$nombre}</strong>, identificado con el documento No. <strong>{$docnum}</strong> expedido en <strong>{$exp}</strong>, con código <strong>{$cPais}</strong>, ha cursado el programa <strong>{$nom_prog}</strong> con las siguientes asignaturas:</p>";

$pdf->writeHTML($html_header, true, false, true, false, '');

// === Table Rendering Function ===
function generateTable($pdf, $headerCells, $cellWidths, $data, $title = '') {
    if (!empty($title)) {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Ln(5);
        $pdf->Cell(0, 10, $title, 0, 1, 'L');
    }

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(230, 230, 230);
    foreach ($headerCells as $i => $header) {
        $pdf->Cell($cellWidths[$i], 7, $header, 1, 0, 'C', 1);
    }
    $pdf->Ln();

    $pdf->SetFont('helvetica', '', 9);
    $fill = 0;
    foreach ($data as $row) {
        $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
        foreach ($headerCells as $i => $header) {
            // aquí decides qué dato mostrar dependiendo del encabezado
            $value = '';
            switch (strtoupper($header)) {
                case 'PERIODO':
                    $value = $row['Persecuencia'] ?? '';
                    break;
                case 'ASIGNATURA':
                    $value = $row['matnombre'] ?? '';
                    break;
                case 'CALIFICACIÓN':
                    $value = $row['Matcursadascalif'] ?? '';
                    break;
                case 'CONDICIÓN':
                    $value = $row['Condcod'] ?? '';
                    break;
                case 'CRÉDITOS':
                    $value = $row['Matcreditos'] ?? '';
                    break;
                case 'HORAS/SEM.':
                    $value = ($row['Matcarrhorteoricas'] ?? 0) + ($row['Matcarrhorpracticas'] ?? 0);
                    break;
                case 'TIPO':
                    $value = $row['Matcursadascalif'] ?? '';
                    break;
                default:
                    $value = '';
            }
            $pdf->Cell($cellWidths[$i], 6, $value, 1, 0, 'C', 1);
        }
        $pdf->Ln();
        $fill = !$fill;
    }
}


// === Column Widths ===
$pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
$cellWidths = [$pageWidth * 0.10, $pageWidth * 0.38, $pageWidth * 0.14, $pageWidth * 0.12, $pageWidth * 0.12, $pageWidth * 0.14];
$headerCells = ['PERIODO', 'ASIGNATURA', 'CALIFICACIÓN', 'CONDICIÓN', 'CRÉDITOS', 'HORAS/SEM.'];

// === Render Tables by Period ===
foreach ($registros_historial as $periodo => $data) {
    $pdf->Ln(5);
    generateTable($pdf, $headerCells, $cellWidths, $data['materias'], "Periodo: $periodo");

    if (isset($data['promedio_semestre'])) {
        $prom_periodo = $data['promedio_semestre'];
        $prom_acumulado = $data['promedio_acumulado'] ?? $prom_periodo;
    
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 6, "Promedio del periodo $periodo: $prom_periodo (" . numletras($prom_periodo) . ")", 0, 1, 'R');
        $pdf->Cell(0, 6, "Promedio acumulado hasta $periodo: $prom_acumulado (" . numletras($prom_acumulado) . ")", 0, 1, 'R');
    }
    
}

// === Materias Homologadas ===
    if (!empty($registros_homologa)) {
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Write(0, "Materias aprobadas por homologación", '', 0, 'L', true, 0, false, false, 0);
    $pdf->SetFont('helvetica', '', 9);

    $cellWidthsH = [$pageWidth * 0.2, $pageWidth * 0.6, $pageWidth * 0.2];
    $headerCellsH = ['PERIODO', 'ASIGNATURA', 'TIPO'];
    $dataH = array_map(function ($r) {
        return [
            'Persecuencia' => $r['Persecuencia'],
            'matnombre' => $r['matnombre'],
            'Matcursadascalif' => $r['Calificacionesestatus'],
            'Condcod' => '',
            'Matcreditos' => '',
            'Matcarrhorteoricas' => 0,
            'Matcarrhorpracticas' => 0
        ];
    }, $registros_homologa);

    generateTable($pdf, $headerCellsH, $cellWidthsH, $dataH);
}

// === Output PDF ===
$pdf->Output('historial_academico_' . $estcod . '.pdf', 'I');
ob_end_flush();

