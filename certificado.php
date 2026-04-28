<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require(__DIR__ . '/pdf/fpdf.php');
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado.");
}

require_once 'conexion.php'; // Asegúrate que aquí se defina $pdo
require_once 'cripto.php';

// 1. Obtener los parámetros
$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$token) {
    die("Error: Faltan parámetros.");
}

$estcod = decryptToken($token);

// 2. Consulta SQL corregida para PDO
$query = "SELECT DISTINCT 
    p.Codpin,    
    TRIM(CONCAT_WS(' ', 
        UPPER(p.pnombr), 
        NULLIF(UPPER(p.snombr), ''), 
        UPPER(p.papell), 
        NULLIF(UPPER(p.sapell), '')
    )) AS NombreCompleto,
    m.Mat_Nombre, 
    h.Persecuencia,
    h.Perano,
    h.Id_Historico 
FROM Persona p
INNER JOIN Persona_estudiante pe ON p.codpin = pe.codpin 
INNER JOIN Historial h ON pe.estcod = h.Estcod
LEFT JOIN Materias m ON h.Matcod = m.Mat_Cod
WHERE h.Id_Historico = ?
LIMIT 1";

$stmt = $pdo->prepare($query);
$stmt->execute([$estcod]); 

// Obtenemos el resultado (Aquí es donde se obtienen los datos)
$reg = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificación: ¿La consulta devolvió algo?
if (!$reg) {
    die("No se encontró registro para este estudiante.");
}

// --- YA NO HACE FALTA LLAMAR A FETCH DE NUEVO ---

// 3. Preparar variables
$Nombresc = $reg['NombreCompleto'];
$Ndoc     = $reg['Codpin'];       // Asignado según tu petición
$Ncurso   = $reg['Mat_Nombre']; 
$ano      = $reg['Perano'];       
$V        = $reg['Persecuencia']; 
$Fecha    = ($V != 1) ? 'Noviembre 30 de ' . $ano : 'Junio 30 de ' . $ano;

// 4. Registro de log (usando sentencias preparadas)
$hoy = date("Y-m-d");

// 5. Preparar variables para el PDF
$Nombresc = $reg['NombreCompleto'];
$Ndoc     = $reg['Codpin']; 
$Ncurso   = $reg['Mat_Nombre']; 

// Lógica de fechas
$V        = $reg['Persecuencia']; 
$Fecha    = ($V != 1) ? 'Noviembre 30 de ' . $ano : 'Junio 30 de ' . $ano;

// 6. Generación del PDF
$pdf = new FPDF('L', 'mm', array(215, 279));
$pdf->AddPage();

// Inserta imagen de fondo (Diploma)
$pdf->Image('Diploma.jpg', 0, 0, 280);

$pdf->Ln(85);
$pdf->SetFont('Arial', 'B', 20);
$pdf->Cell(20);
$pdf->Cell(221, 8, utf8_decode($Nombresc), 0, 1, 'C');

$pdf->Ln(-1);
$pdf->Cell(20);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(221, 5, utf8_decode("Documento: " . $Ndoc), 0, 1, 'C');

$pdf->Ln(4);
$pdf->Cell(20);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(221, 10, utf8_decode("Asistió y Aprobó el curso de: "), 0, 1, 'C');

$pdf->Ln(6);
$pdf->Cell(20);
$pdf->SetFont('Arial', 'B', 20);
$pdf->Cell(221, 10, utf8_decode($Ncurso), 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 16);
$pdf->Ln(16);
$pdf->Cell(20);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(130, 10, utf8_decode("24"), 0, 1, 'R');

$pdf->Ln(-4);
$pdf->Cell(20);
// Ajuste de posición de fecha
$pdf->Cell(320, 10, utf8_decode("Bucaramanga                    ". $Fecha), 0, 1, 'C');

$pdf->Output();
?>