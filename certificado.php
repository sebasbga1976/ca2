<?php
session_start();
$hoy = date("Y-m-d");
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
    c.Codpin,    
    c.Nombre_Completo AS NombreCompleto,
    m.Mat_Nombre, 
    h.Persecuencia,
    h.Perano,
    h.Id_Historico 
FROM Cliente c
INNER JOIN Cliente_Estudiante ce ON c.Codpin = ce.CodPin 
INNER JOIN Historial h ON ce.EstCod = h.Estcod
INNER JOIN Materias m ON h.Matcod = m.Mat_Cod
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

// 5. Preparar variables para el PDF
$Nombresc = $reg['NombreCompleto'];
$Ndoc     = $reg['Codpin']; 
$Ncurso   = $reg['Mat_Nombre'];
$ano      = $reg['Perano'];

// Lógica de fechas
$V        = substr($reg['Persecuencia'], -1); ; 
$Fecha    = ($V != 1) ? 'Noviembre 30 de ' . $ano : 'Junio 30 de ' . $ano;

// 6. Generación del PDF (Horizontal: 279mm)
$pdf = new FPDF('L', 'mm', array(215, 279));
$pdf->AddPage();
$pdf->Image('Diploma.jpg', 0, 0, 279); // Ajustado al ancho total

// Definimos el ancho útil para centrar
$ancho_util = 259; 

// --- NOMBRE DEL ESTUDIANTE ---
$pdf->SetY(85); // Posición vertical inicial
$pdf->SetFont('Arial', 'B', 26); // Un poco más grande para destacar
$pdf->Cell($ancho_util, 12, utf8_decode($Nombresc), 0, 1, 'C');

// --- DOCUMENTO ---
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell($ancho_util, 6, utf8_decode("Identificación: " . $Ndoc), 0, 1, 'C');

$pdf->Ln(6); // Espacio de separación

// --- TEXTO FIJO ---
$pdf->SetFont('Arial', '', 14); // Normal, no negrita para variar
$pdf->Cell($ancho_util, 10, utf8_decode("Asistió y Aprobó el curso de: "), 0, 1, 'C');

// --- NOMBRE DEL CURSO ---
$pdf->SetFont('Arial', 'B', 22);
$pdf->Cell($ancho_util, 12, utf8_decode($Ncurso), 0, 1, 'C');

// --- INTENSIDAD HORARIA ---
$pdf->Ln(2); 
$pdf->SetFont('Arial', '', 12);
$pdf->Cell($ancho_util, 10, utf8_decode("Con una intensidad de 24 Horas."), 0, 1, 'C');

// --- FECHA Y CIUDAD (PIE DEL DIPLOMA) ---
$pdf->SetY(140); // Lo bajamos para que quede cerca de la zona de firmas
$pdf->SetFont('Arial', '', 12);
$pdf->Cell($ancho_util, 10, utf8_decode("En constancia se firma en la ciudad de Bucaramanga Fecha: " . $Fecha), 0, 1, 'C');

$pdf->Output();
?>