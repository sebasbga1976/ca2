<?php
// Define una clave secreta fuerte. 
// ¡IMPORTANTE!: No la pongas en texto plano en GitHub. Usa una variable de entorno.
define('ENCRYPTION_KEY', 'lacigüeña'); 
define('METHOD', 'aes-256-cbc');

function encryptToken($data) {
    $ivLength = openssl_cipher_iv_length(METHOD);
    $iv = openssl_random_pseudo_bytes($ivLength);
    
    $encrypted = openssl_encrypt($data, METHOD, ENCRYPTION_KEY, 0, $iv);
    
    // Unimos el IV al final del texto cifrado (lo necesitaremos para descifrar)
    // Luego lo convertimos a Base64 y lo hacemos seguro para URL
    $token = base64_encode($encrypted . '::' . $iv);
    return str_replace(['+', '/', '='], ['-', '_', ''], $token);
}

function decryptToken($token) {
    // Revertimos el formato URL-safe a Base64 original
    $token = str_replace(['-', '_'], ['+', '/'], $token);
    $decoded = base64_decode($token);
    
    // Separamos el dato del IV
    $parts = explode('::', $decoded, 2);
    if (count($parts) !== 2) return null;
    
    list($encryptedData, $iv) = $parts;
    
    return openssl_decrypt($encryptedData, METHOD, ENCRYPTION_KEY, 0, $iv);
}