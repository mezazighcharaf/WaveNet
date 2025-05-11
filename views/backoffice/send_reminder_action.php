<?php
session_start();
require_once '../Config/database.php';
require_once '../Model/ParticipantBackModel.php';
require_once '../Controller/sendReminder.php';

// Vérifie si l'id_participant est envoyé
if (isset($_POST['id_participant'])) {
    $participantModel = new ParticipantBackModel();
    $participant = $participantModel->getParticipantById($_POST['id_participant']);

    if ($participant) {
        $participantEmail = $participant['email_participant'];
        $participantName = $participant['nom_participant'];
        $eventDate = date('Y-m-d ', strtotime('+3 days')); // Exemple : événement dans 3 jours

        // Envoie de l'email
        sendReminder($participantEmail, $participantName, $eventDate);

        $_SESSION['success'] = "Rappel envoyé à " . htmlspecialchars($participantName) . " avec succès.";
    } else {
        $_SESSION['error'] = "Participant introuvable.";
    }
} else {
    $_SESSION['error'] = "ID du participant non spécifié.";
}

// Redirige vers la page principale
header('Location: eco_actionsB.php');
exit;
?>
