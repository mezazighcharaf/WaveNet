<?php
// Inclure Composer autoload pour PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
  // Inclure Composer autoload

// Fonction pour envoyer un rappel par e-mail
function sendReminder($toEmail, $participantName, $eventDate) {
    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';  // Utiliser ton serveur SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'f4864ab6bf986c';  // Remplace par ton utilisateur SMTP
        $mail->Password = 'edcb10e41335d7';  // Remplace par ton mot de passe SMTP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 2525;

        // Paramètres de l'e-mail
        $mail->setFrom('boutaieb03yosr@gmail.com', 'Your App');
        $mail->addAddress($toEmail, $participantName);

        // Contenu de l'e-mail
        $mail->isHTML(true);
        $mail->Subject = 'Reminder: Upcoming Event';
        $mail->Body    = "Hello $participantName,<br><br>Just a reminder that you have an event scheduled for <strong>$eventDate</strong>.<br>Don't forget to join us!";

        // Envoi de l'e-mail
        $mail->send();
        echo 'Reminder sent successfully';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
