<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit(); // Es vital usar exit después de un header
}
require_once('tcpdf/tcpdf.php');
require 'numletras.php';
include "conexion.php";
require_once 'cripto.php'; 

$estcod = decryptToken($_REQUEST['token']) ?? die("Error: Código no especificado.");

// --- 1. Consulta ---
$stmt = $pdo->prepare("SELECT ce.Estcod, CONCAT(c.PNombre, ' ', c.SNombre, ' ', c.PApellido, ' ', c.SApellido) AS Nombre, c.Codpin, c.Exp, car.Nombre_Programa 
                       FROM cli_est ce 
                       INNER JOIN Cliente c ON c.Codpin = ce.Codpin
                       INNER JOIN Estudiante e ON ce.Estcod = e.Estcod
                       INNER JOIN Carreras car ON e.carrcod = car.CarrCod 
                       WHERE ce.Estcod = ?");
$stmt->execute([$estcod]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt_h = $pdo->prepare("SELECT h.Persecuencia, m.Mat_Nombre, h.Matcursadascalif, h.Matcreditos, 
                                h.Condcod, h.Matcarrhorteoricas, h.Matcarrhorpracticas, h.Calificacionesestatus, h.Matcursadascaracter
                        FROM Historial h
                        INNER JOIN Materias m ON m.Mat_Cod = h.Matcod
                        WHERE h.Estcod = ?
                        ORDER BY h.Persecuencia ASC, m.Mat_Nombre ASC");
$stmt_h->execute([$estcod]);
$raw_data = $stmt_h->fetchAll(PDO::FETCH_ASSOC);

// --- 2. Clase PDF ---
class MYPDF extends TCPDF {
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'R');
    }
}

$pdf = new MYPDF('P', 'mm', 'Letter');
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

// --- 3. Cabecera Dinámica (Basada en tu imagen) ---
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'UNIVERSIDAD DE SANTANDER', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 7, 'NIT 804.001.890-1', 0, 1, 'L');
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'CERTIFICADO DE HISTORIAL ACADÉMICO', 0, 1, 'L');
$pdf->Ln(5);

// --- Bloque de texto dinámico corregido ---
$texto_certificacion = "Se certifica que el estudiante <b>" . mb_strtoupper($info['Nombre']) . "</b>, " . 
                       "identificado con el documento No. <b>" . $info['Codpin'] . "</b>, " . 
                       "expedido en <b>" . $info['Exp'] . "</b>, " . 
                       "con código <b>" . $info['Estcod'] . "</b>, " . 
                       "ha cursado el programa <b>" . $info['Nombre_Programa'] . "</b> " . 
                       "con las siguientes asignaturas:";

// Definimos la fuente antes de imprimir el HTML
$pdf->SetFont('helvetica', '', 11);

// Usamos writeHTMLCell para que procese las etiquetas <b>...</b>
// writeHTMLCell($w, $h, $x, $y, $html, $border, $ln, $fill, $reseth, $align)
$pdf->writeHTMLCell(0, 0, '', '', $texto_certificacion, 0, 1, 0, true, 'J');

$pdf->Ln(5);

// --- 4. Procesamiento de Tabla (Igual que antes) ---
$global_sum_ponderada = 0;
$global_creditos = 0;
$data_agrupada = [];
foreach ($raw_data as $row) { $data_agrupada[$row['Persecuencia']][] = $row; }

$columnas = ['PERIODO'=>'Persecuencia', 'ASIGNATURA'=>'Mat_Nombre', 'CALIF.'=>'Matcursadascalif', 'CRÉD.'=>'Matcreditos', 'HORAS'=>'HORAS'];
$ancho_col = 185 / count($columnas);

foreach ($data_agrupada as $periodo => $materias) {
    
    // Control de paginación para mantener el bloque unido
    $num_filas = count($materias);
    $altura_bloque = 15 + ($num_filas * 10) + 15;
    if (($pdf->GetY() + $altura_bloque) > ($pdf->getPageHeight() - 25)) $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 8, "Periodo: $periodo", 0, 1, 'L');
    $pdf->SetFillColor(230, 230, 230);
    foreach ($columnas as $tit => $k) $pdf->Cell($ancho_col, 8, $tit, 1, 0, 'C', 1);
    $pdf->Ln();

    $sem_sum_ponderada = 0;
    $sem_creditos = 0;

    foreach ($materias as $row) {
        if (in_array($row['Matcursadascaracter'], ['A', 'R'])) {
                $row['Matcursadascalif'] = $row['Matcursadascaracter'];
            }
        if ($row['Calificacionesestatus'] !== 'AT' && $row['Matcreditos'] > 0) {
            $sem_sum_ponderada += ((float)$row['Matcursadascalif'] * (int)$row['Matcreditos']);
            $sem_creditos += (int)$row['Matcreditos'];
            $global_sum_ponderada += ((float)$row['Matcursadascalif'] * (int)$row['Matcreditos']);
            $global_creditos += (int)$row['Matcreditos'];
        }

        foreach ($columnas as $tit => $k) {
            $val = ($tit == 'HORAS') ? ($row['Matcarrhorteoricas'] + $row['Matcarrhorpracticas']) : $row[$k];
            $pdf->MultiCell($ancho_col, 10, (string)$val, 1, 'C', 0, 0, '', '', true, 0, false, true, 10, 'M');
        }
        $pdf->Ln();
    }

    $prom_sem = ($sem_creditos > 0) ? round($sem_sum_ponderada / $sem_creditos, 2) : 0;
    $prom_acum = ($global_creditos > 0) ? round($global_sum_ponderada / $global_creditos, 2) : 0;

    $pdf->Ln(2);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 6, "Promedio del periodo $periodo: " . number_format($prom_sem, 2) . " " . numletras($prom_sem) . " ", 0, 1, 'R');
    $pdf->Cell(0, 6, "Promedio acumulado hasta $periodo: " . number_format($prom_acum, 2) . " " . numletras($prom_acum) . " ", 0, 1, 'R');
    $pdf->Ln(5);
}

$pdf->Output('historial_' . $estcod . '.pdf', 'I');