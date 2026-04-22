<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

class EmailService {
    private $config;

    public function __construct() {
        $this->config = require 'email_config.php';
    }

    private function crearMailer() {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $this->config['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $this->config['smtp_user'];
        $mail->Password   = $this->config['smtp_pass'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $this->config['smtp_port'];
        $mail->setFrom($this->config['from_email'], $this->config['from_name']);
        $mail->CharSet    = 'UTF-8';
        return $mail;
    }

    public function enviarBienvenida($destinatario, $nombre) {
        // Definimos la URL localmente para que la función pueda verla
        $url_login = "http://172.17.5.85/"; 
        
        $mail = $this->crearMailer();
        $mail->addAddress($destinatario);
        $mail->isHTML(true);
        $mail->Subject = 'Bienvenido a nuestro sistema';
        
        // Usamos $destinatario en lugar de $Correo porque es el parámetro que ya recibes
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h1 style='color: #333;'>¡Hola, $nombre!</h1>
            <p>Tu cuenta ha sido creada exitosamente en nuestra plataforma.</p>
            
            <p><strong>Datos de acceso:</strong><br>
            Correo: <strong>$destinatario</strong></p>
            
            <p>Haz clic en el siguiente botón para iniciar sesión:</p>
            
            <a href='$url_login' style='background-color: #007bff; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                Iniciar Sesión
            </a>
            
            <p>Si no solicitaste este registro, por favor ignora este correo.</p>
        </div>";            
        return $mail->send();
    }

    // Método para Restablecer Contraseña
    public function enviarRestablecimiento($destinatario, $token) {
        $mail = $this->crearMailer();
        $mail->addAddress($destinatario);
        $mail->isHTML(true);
        $mail->Subject = 'Restablecer contraseña';
        $mail->Body    = "Hola, tu contraseña temporal es: " . $token . 
                   "\nPor favor, cámbiala inmediatamente después de iniciar sesión.";
        return $mail->send();
    }
}