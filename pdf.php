<?php
session_start();
if (!isset($_SESSION['usuario_id'])){
    header("Location: index.php");
    exit(); // Es vital usar exit después de un header
}
require_once('tcpdf/tcpdf.php');
ob_start(); 

include "fecha.php";
include "conexion.php";
require_once 'cripto.php';
require 'numletras.php';

$estcod = decryptToken($_REQUEST['token']) ?? die("No se ha especificado un código de estudiante.");

// 2. Consulta de información del estudiante
$query_info = "SELECT ce.EstCod, CONCAT(c.PNombre, ' ', c.SNombre, ' ', c.PApellido, ' ', c.SApellido) AS Nombre, c.Codpin, c.Exp, c2.Nombre_Programa
               FROM Cliente_Estudiante ce
               INNER JOIN Cliente c ON c.Codpin = ce.Codpin
               INNER JOIN Carreras c2 ON ce.Carr_Cod = c2.CarrCod
               WHERE ce.EstCod = ?";
$stmt_info = $pdo->prepare($query_info);
$stmt_info->bindParam(1, $estcod, PDO::PARAM_STR);
$stmt_info->execute();
$info = $stmt_info->fetch(PDO::FETCH_ASSOC);

if (!$info) {
    die("No se encontró información para el estudiante.");
}

$docnum = $info['Codpin'];
$nombre = $info['Nombre'];
$exp = $info['Exp'];
$cPais = $info['EstCod'];
$nom_prog = $info['Nombre_Programa'] ?? 'N/A'; // Corregido: antes decía Nombre_Largo

// 3. Obtención de datos del Historial
$query_historial = "SELECT h.Perano, h.Persecuencia, h.Matcod, m.Mat_Nombre, h.Condcod, ROUND(h.Matcursadascalif, 1) AS Matcursadascalif,
                           h.Matcreditos, h.Matcarrhorteoricas, h.Matcarrhorpracticas, h.Calificacionesestatus,
                           ROUND((h.Matcreditos * h.Matcursadascalif), 1) AS producto
                    FROM Historial h
                    INNER JOIN Materias m ON m.Mat_Cod = h.Matcod
                    WHERE h.Estcod = ?
                    ORDER BY h.Persecuencia, m.Mat_Nombre";

$stmt_historial = $pdo->prepare($query_historial);
$stmt_historial->bindParam(1, $cPais, PDO::PARAM_STR);
$stmt_historial->execute();
$result_historial = $stmt_historial->fetchAll(PDO::FETCH_ASSOC);

$registros_por_periodo = [];
$prod_total = 0.0;
$creditos_total = 0;
$promedio_acumulado = 0.0;

foreach ($result_historial as $reg) {
    $id_periodo = substr($reg['Perano'], 0, 2) . $reg['Persecuencia'];
    if (!isset($registros_por_periodo[$id_periodo])) {
        $registros_por_periodo[$id_periodo] = ['materias' => [], 'prod_sem' => 0.0, 'cred_sem' => 0];
    }
    $registros_por_periodo[$id_periodo]['materias'][] = $reg;

    if ($reg['Calificacionesestatus'] !== 'AT' && $reg['Matcreditos'] > 0) {
        $prod_total += (float)$reg['producto'];
        $creditos_total += (int)$reg['Matcreditos'];
        $registros_por_periodo[$id_periodo]['prod_sem'] += (float)$reg['producto'];
        $registros_por_periodo[$id_periodo]['cred_sem'] += (int)$reg['Matcreditos'];
    }
}
if ($creditos_total > 0) $promedio_acumulado = round($prod_total / $creditos_total, 2);

// Consultar homologaciones
$query_hom = "SELECT h.Persecuencia, m.Mat_Nombre, h.Calificacionesestatus FROM Historial h 
              INNER JOIN Materias m ON m.Mat_Cod = h.Matcod 
              WHERE h.Estcod = ? AND h.Calificacionesestatus LIKE 'AT%' ORDER BY m.Mat_Nombre";
$stmt_hom = $pdo->prepare($query_hom);
$stmt_hom->bindParam(1, $cPais, PDO::PARAM_STR);
$stmt_hom->execute();
$registros_homologa = $stmt_hom->fetchAll(PDO::FETCH_ASSOC);

function generateTable($pdf, $headerCells, $cellWidths, $data, $printHeader = true) {
    $drawHeader = function($pdf, $headerCells, $cellWidths) {
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(240, 240, 240);
        foreach ($headerCells as $i => $label) {
            $pdf->Cell($cellWidths[$i], 7, $label, 1, 0, 'C', 1);
        }
        $pdf->Ln();
        $pdf->SetFont('helvetica', '', 8);
    };

    if ($printHeader) $drawHeader($pdf, $headerCells, $cellWidths);

    $fill = 0;
    foreach ($data as $row) {
        if ($pdf->GetY() > ($pdf->getPageHeight() - 30)) {
            $pdf->AddPage();
            $drawHeader($pdf, $headerCells, $cellWidths);
        }

        $pdf->SetFillColor($fill ? 248 : 255);
        foreach ($headerCells as $i => $label) {
            $content = '';
            switch ($label) {
                case 'PERIODO': $content = $row['Persecuencia'] ?? ''; break;
                case 'ASIGNATURA CURSADA': $content = $row['Mat_Nombre'] ?? ''; break; // Ajustado índice
                case 'CALIFICACIÓN': $content = (string)($row['Matcursadascalif'] ?? ''); break;
                case 'CONDICIÓN': $content = $row['Condcod'] ?? ''; break;
                case 'CREDITOS': $content = (string)($row['Matcreditos'] ?? ''); break;
                case 'HORAS/SEM.': $content = (string)(($row['Matcarrhorteoricas'] ?? 0) + ($row['Matcarrhorpracticas'] ?? 0)); break;
                case 'TIPO HOMOLOGACIÓN': $content = $row['Calificacionesestatus'] ?? ''; break;
            }
            $pdf->MultiCell($cellWidths[$i], 6, $content, 1, 'L', $fill, 0, '', '', true, 0, false, true, 0, 'T');
        }
        $pdf->Ln();
        $fill = !$fill;
    }
}

// 4. Creación del PDF
$pdf = new TCPDF('P', 'mm', 'Letter', true, 'UTF-8', false);
$pdf->SetMargins(15, 20, 15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->AddPage();

$pdf->writeHTML("<div style='text-align:center;'><strong>UNIVERSIDAD DE SANTANDER</strong><br>NIT 804.001.890-1<br><strong>CERTIFICA</strong></div><br>", true, false, true, false, '');
$pdf->writeHTML("<p>Que <strong>$nombre</strong> con C.C. <strong>$docnum</strong> de <strong>$exp</strong> obtuvo las siguientes calificaciones en <strong>$nom_prog</strong>:</p>", true, false, true, false, '');

$pageWidth = $pdf->getPageWidth() - 30;
$w = [$pageWidth*0.08, $pageWidth*0.40, $pageWidth*0.12, $pageWidth*0.15, $pageWidth*0.1, $pageWidth*0.15];
$h = ['PERIODO', 'ASIGNATURA CURSADA', 'CALIFICACIÓN', 'CONDICIÓN', 'CREDITOS', 'HORAS/SEM.']; // Línea corregida

foreach ($registros_por_periodo as $per => $datos) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(0, 8, "Periodo: $per", 0, 1, 'L');
    generateTable($pdf, $h, $w, $datos['materias']);
    
    if ($datos['cred_sem'] > 0) {
        $ps = round($datos['prod_sem'] / $datos['cred_sem'], 2);
        $pdf->SetFont('helvetica', 'I', 9);
        $pdf->Cell(array_sum($w), 6, "Promedio Periodo: $ps " . numletras($ps) . "", 1, 1, 'R');
    }
    $pdf->Ln(4);
}

if ($promedio_acumulado > 0) {
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 10, "PROMEDIO PONDERADO ACUMULADO: $promedio_acumulado " . numletras($promedio_acumulado) . "", 0, 1, 'R');
}

if (!empty($registros_homologa)) {
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 10, 'MATERIAS POR HOMOLOGACIÓN', 0, 1, 'L');
    $wH = [$pageWidth*0.2, $pageWidth*0.5, $pageWidth*0.3];
    $hH = ['PERIODO', 'ASIGNATURA CURSADA', 'TIPO HOMOLOGACIÓN'];
    
    // Corregido: Mat_Nombre en lugar de matnombre
    $dataH = array_map(fn($r) => [
        'Persecuencia'=>$r['Persecuencia'], 
        'Mat_Nombre'=>$r['Mat_Nombre'], 
        'Calificacionesestatus'=>$r['Calificacionesestatus']
    ], $registros_homologa);
    
    generateTable($pdf, $hH, $wH, $dataH);
}

ob_end_clean();
$pdf->Output('Historial_Academico.pdf', 'I');
?>