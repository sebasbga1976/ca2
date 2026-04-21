<?php
function numletras($ntot) {
    // Aseguramos que el número tenga formato correcto y separamos entero y decimal
    $partes = explode('.', number_format((float)$ntot, 2, '.', ''));
    $entero = (int)$partes[0];
    $decimal = $partes[1];

    $unidades = ['cero', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
    
    // Convertir parte entera (solo unidades según tu ejemplo 0-5)
    $textoEntero = $unidades[$entero] ?? 'número fuera de rango';
    
    // Función lógica para los decimales
    $textoDecimal = convertirDecimal($decimal);

    return "($textoEntero punto $textoDecimal)";
}

function convertirDecimal($num) {
    $n = (int)$num;
    if ($n == 0) return "cero";
    
    // Casos especiales y base
    $mapa = [
        0 => '', 1 => 'uno', 2 => 'dos', 3 => 'tres', 4 => 'cuatro', 5 => 'cinco',
        6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve', 10 => 'diez',
        11 => 'once', 12 => 'doce', 13 => 'trece', 14 => 'catorce', 15 => 'quince',
        20 => 'veinte', 30 => 'treinta', 40 => 'cuarenta', 50 => 'cincuenta',
        60 => 'sesenta', 70 => 'setenta', 80 => 'ochenta', 90 => 'noventa'
    ];

    // Si está en el mapa, devolver directo
    if (isset($mapa[$n])) return $mapa[$n];

    // Lógica para números compuestos (ej. 21-29, 31-39, etc.)
    if ($n > 10 && $n < 20) return "diez y " . $mapa[$n - 10];
    if ($n > 20 && $n < 30) return "veinti" . $mapa[$n - 20];
    
    // Para el resto (31-99)
    $decena = floor($n / 10) * 10;
    $unidad = $n % 10;
    return $mapa[$decena] . " y " . $mapa[$unidad];
}
?>