<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use App\SimpleLogger;

$logger = new SimpleLogger(__DIR__.'/logs/app.log');

$mail = new PHPMailer();
// For real SMTP support install official phpmailer/phpmailer via Composer.
// This minimal example uses PHP mail() backend.
$mail->setFrom('tu_correo@ejemplo.com', 'Tu Nombre');
$mail->addAddress('destinatario@ejemplo.com', 'Destinatario');
$mail->isHTML(true);
$mail->Subject = 'Prueba desde proyecto ejemplo';
$mail->Body = '<b>Hola</b>, este es un correo de prueba usando un stub mínimo de PHPMailer.';
$mail->AltBody = 'Hola, este es un correo de prueba usando un stub mínimo de PHPMailer.';

try {
    $mail->send();
    echo "Mensaje enviado (usando mail()).\n";
    $logger->info('Correo enviado correctamente', ['to' => 'destinatario@ejemplo.com']);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    $logger->error('Error enviando correo', ['exception' => $e->getMessage()]);
}
