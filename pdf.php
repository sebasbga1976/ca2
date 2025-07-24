<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Ensure TCPDF is loaded before any output
require_once('tcpdf/tcpdf.php');
ob_start(); // Start output buffering

include "fecha.php";
include "conexion.php";
require 'numletras.php';

$estcod = $_REQUEST['estcod'] ?? die("No se ha especificado un código de estudiante.");
$fechaactual = date("dmY");

$query_info = "SELECT ce.EstCod, CONCAT(c.PNombre, ' ', c.SNombre, ' ', c.PApellido, ' ', c.SApellido) AS Nombre, c.Codpin, c.Exp
               FROM Cliente_Estudiante ce
               INNER JOIN Cliente c ON c.Codpin = ce.Codpin
               WHERE ce.EstCod = ?";
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
$nom_prog = $_REQUEST['NomProg'] ?? 'N/A';

$registros_historial = [];
$registros_homologa = [];
$promedio_acumulado = 0.0;
$per_ano_acad_acumulado = '';
$periodo_actual_acumulado = '';

if (strlen($cPais) > 8) {
    $query_historial = "SELECT h.Perano, h.Persecuencia, h.Matcod, m.Mat_Nombre, h.Condcod, ROUND(h.Matcursadascalif, 1) AS Matcursadascalif,
                               h.Matcursadascaracter, h.Matcreditos, h.Matcarrhorteoricas, h.Matcarrhorpracticas, h.Calificacionesestatus
                        FROM Historial h
                        INNER JOIN Materias m ON m.Mat_Cod = h.Matcod
                        WHERE h.Estcod = ? AND h.Carrlapsonro IN ('INF', 'IDIO', 'BIEN')
                        ORDER BY h.Persecuencia, m.Mat_Nombre";
    $stmt_historial = $pdo->prepare($query_historial);
    $stmt_historial->bindParam(1, $cPais, PDO::PARAM_STR);
    $stmt_historial->execute();
    $registros_historial = $stmt_historial->fetchAll(PDO::FETCH_ASSOC);
} else {
    $query_historial = "SELECT h.Perano, h.Persecuencia, h.Matcod, m.Mat_Nombre, h.Condcod, ROUND(h.Matcursadascalif, 1) AS Matcursadascalif,
                               h.Matcursadascaracter, h.Matcreditos, h.Matcarrhorteoricas, h.Matcarrhorpracticas, h.Calificacionesestatus,
                               ROUND((h.Matcreditos * h.Matcursadascalif), 1) AS producto
                        FROM Historial h
                        INNER JOIN Materias m ON m.Mat_Cod = h.Matcod
                        WHERE h.Estcod = ?
                        ORDER BY h.Persecuencia, m.Mat_Nombre";
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

    foreach ($result_historial as $reg) {
        $per_ano_acad = substr($reg['Perano'], 0, 2);

        if ($periodo_actual !== $reg['Persecuencia']) {
            if ($sum_cred_semestre > 0) {
                $registros_por_periodo[$per_ano_acad . $periodo_actual]['promedio_semestre'] = round($prod_semestre / $sum_cred_semestre, 2);
            }
            $prod_semestre = 0.0;
            $sum_cred_semestre = 0;
            $periodo_actual = $reg['Persecuencia'];
            $per_ano_acad_acumulado = $per_ano_acad;
            $periodo_actual_acumulado = $periodo_actual;
            $registros_por_periodo[$per_ano_acad . $periodo_actual]['materias'] = [];
        }

        $registros_por_periodo[$per_ano_acad . $periodo_actual]['materias'][] = $reg;

        if ($reg['Calificacionesestatus'] !== 'AT' && $reg['Matcreditos'] > 0) {
            $prod_total += $reg['producto'];
            $creditos_total += $reg['Matcreditos'];
            $prod_semestre += $reg['producto'];
            $sum_cred_semestre += $reg['Matcreditos'];
        }
    }

    if ($creditos_total > 0) {
        $promedio_acumulado = round($prod_total / $creditos_total, 2);
    }
    if ($sum_cred_semestre > 0) {
        $registros_por_periodo[$per_ano_acad . $periodo_actual]['promedio_semestre'] = round($prod_semestre / $sum_cred_semestre, 2);
    }
    $registros_historial = $registros_por_periodo;

    // Consultar materias homologadas
    $query_homologa = "SELECT h.Persecuencia, m.Mat_Nombre, h.Calificacionesestatus
                       FROM Historial h
                       INNER JOIN Materias m ON m.Mat_Cod = h.Matcod
                       WHERE h.Estcod = ? AND h.Calificacionesestatus LIKE 'AT%'
                       ORDER BY m.Mat_Nombre ASC";
    $stmt_homologa = $pdo->prepare($query_homologa);
    $stmt_homologa->bindParam(1, $cPais, PDO::PARAM_STR);
    $stmt_homologa->execute();
    $registros_homologa = $stmt_homologa->fetchAll(PDO::FETCH_ASSOC);
}

// Crear nuevo PDF usando TCPDF
$pdf = new TCPDF('P', 'mm', 'Letter', true, 'UTF-8', false);  // Orientación Portrait y tamaño Carta
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Universidad de Santander');
$pdf->SetTitle('Historial Académico');
$pdf->SetSubject('Historial Académico del Estudiante');
$pdf->SetKeywords('Historial, Académico, Estudiante, UDES');
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();

$html_header = "<div>
    <p style=\"width: 100%; text-align: center;\">
        <strong>UNIVERSIDAD DE SANTANDER</strong><br>
        NIT 804.001.890-1<br>
        <strong>CERTIFICA</strong>
    </p><br>
    <p style=\"width: 100%;\">
        Que <strong>{$nombre}</strong> con documento de identificación No. <strong>{$docnum}</strong>
        de <strong>{$exp}</strong> y código de estudiante <strong>{$cPais}</strong>
        obtuvo las siguientes calificaciones en el Programa <strong>{$nom_prog}</strong>.
    </p>
    <p>Que NERVEN STEVEN MARTINEZ VILLAMIZAR con documento de identificación No. 1000621913 de Exp y código de estudiante 16381043 obtuvo las siguientes calificaciones en el Programa N/A.</p>
</div>";

$pdf->writeHTML($html_header, true, false, true, false, '');
$pdf->SetFont('helvetica', '', 9);
$pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
$cellWidths = [
    $pageWidth * 0.075,  // 7.5% (antes era 15%)
    $pageWidth * 0.40,  // 40%
    $pageWidth * 0.25,  // 25%
    $pageWidth * 0.10,  // 10%
    $pageWidth * 0.10   // 10%
];

$headerCells = ['PERIODO', 'ASIGNATURA CURSADA', 'CALIFICACIÓN', 'CREDITOS', 'HORAS/SEM.'];
$firstTableGenerated = false; // Variable para controlar si la primera tabla ya se generó

function generateTable($pdf, $headerCells, $cellWidths, $data, $printHeader = true) {
    $numCols = count($headerCells);
    if ($printHeader) {
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.3);

        for ($i = 0; $i < $numCols; $i++) {
            $pdf->Cell($cellWidths[$i], 7, $headerCells[$i], 1, 0, 'C', 1);
        }
        $pdf->Ln();
        $pdf->SetFont('helvetica', '', 9);
    }

    $fill = 0;
    foreach ($data as $row) {
        $pdf->SetFillColor($fill ? 248 : 255, $fill ? 248 : 255, $fill ? 248 : 255); // Alternar colores de fila
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.2);
        for ($i = 0; $i < $numCols; $i++) {
            $cellContent = '';
            switch ($headerCells[$i]) {
                case 'PERIODO':
                    $cellContent = $row['Persecuencia'] ?? '';
                    break;
                case 'ASIGNATURA CURSADA':
                    $cellContent = $row['Mat_Nombre'] ?? '';
                    break;
                case 'CALIFICACIÓN':
                    $cellContent = isset($row['Matcursadascalif']) ? (string)$row['Matcursadascalif'] . " " . ($row['Condcod'] ?? '') : '';
                    break;
                case 'CREDITOS':
                    $cellContent = $row['Matcreditos'] ?? '';
                    break;
                case 'HORAS/SEM.':
                    $cellContent = isset($row['Matcarrhorteoricas'], $row['Matcarrhorpracticas']) ? (string)($row['Matcarrhorteoricas'] + $row['Matcarrhorpracticas']) : '';
                    break;
            }
            $pdf->MultiCell($cellWidths[$i], 6, $cellContent, 1, 'L', $fill, 0, '', '', true, 0, false, true, 0, 'T');
        }
        $pdf->Ln();
        $fill = !$fill;
    }
}

if (strlen($cPais) > 8) {
    generateTable($pdf, $headerCells, $cellWidths, $registros_historial);
} else {
    foreach ($registros_historial as $periodo => $data) {
        if (!$firstTableGenerated) {
            $pdf->Ln(10);
            generateTable($pdf, $headerCells, $cellWidths, $data['materias']);
            if (isset($data['promedio_semestre'])) {
                $pdf->Cell(array_sum($cellWidths), 6, 'Promedio semestre (' . htmlspecialchars($periodo) . '): ' . htmlspecialchars((string) $data['promedio_semestre']) . ' ' . numletras($data['promedio_semestre']), 1, 1, 'R');
            }
            $firstTableGenerated = true; // Marca la primera tabla como generada
        } else {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->writeHTML('<p><strong>Periodo: ' . htmlspecialchars($periodo) . '</strong></p>', true, false, true, false, '');
            generateTable($pdf, $headerCells, $cellWidths, $data['materias'], false); // No imprimir encabezado de nuevo
             if (isset($data['promedio_semestre'])) {
                $pdf->Cell(array_sum($cellWidths), 6, 'Promedio semestre (' . htmlspecialchars($periodo) . '): ' . htmlspecialchars((string) $data['promedio_semestre']) . ' ' . numletras($data['promedio_semestre']), 1, 1, 'R');
            }
        }
    }
    if ($promedio_acumulado > 0) {
        $pdf->SetFont('helvetica', '', 10, 'B');
        $pdf->writeHTML('<p style="text-align: right;"><strong>Promedio ponderado acumulado: ' . htmlspecialchars((string) $promedio_acumulado) . ' ' . numletras($promedio_acumulado) . '</strong></p>', true, false, true, false, '');
    }
}

if (!empty($registros_homologa)) {
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 10, 'B');
    $pdf->writeHTML('<h3>Materias aprobadas por homologación</h3><p>Los cursos relacionados se aprobaron por homologación:</p>', true, false, true, false, '');
    $pdf->SetFont('helvetica', '', 9);
    $cellWidthsHomologa = [$pageWidth * 0.20, $pageWidth * 0.60, $pageWidth * 0.20];
    $headerCellsHomologa = ['PERIODO', 'ASIGNATURA CURSADA', 'TIPO HOMOLOGACIÓN'];
    $dataHomologa = [];
    foreach ($registros_homologa as $reg_homo) {
        $dataHomologa[] = [
            'Persecuencia' => $reg_homo['Persecuencia'],
            'Mat_Nombre' => $reg_homo['Mat_Nombre'],
            'Calificacionesestatus' => $reg_homo['Calificacionesestatus']
        ];
    }
    generateTable($pdf, $headerCellsHomologa, $cellWidthsHomologa, $dataHomologa);
}

// Output the PDF to the browser
$pdf->Output('historial_academico_' . $estcod . '.pdf', 'I');
ob_end_flush();
?>
