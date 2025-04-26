<?php
session_start();
require_once '../Model/participantModel.php';  // Inclure le modèle

$model = new ParticipantModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];  // Récupérer l'action (participer ou annuler)

    // Si l'action est "ajouter", on ajoute un participant
    if ($action === 'add') {
        // Validation des champs
        if (empty($_POST['nom']) || empty($_POST['email'])) {
            $_SESSION['error'] = "Tous les champs doivent être remplis.";
            header('Location: ../View/admin_participants.php');
            exit();
        }

        // Ajouter le participant
        $success = $model->addParticipant($_POST['nom'], $_POST['email']);
        
        if ($success) {
            $_SESSION['success'] = "Participant ajouté avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du participant.";
        }

        header('Location: ../View/admin_participants.php');
        exit();
    }

    // Si l'action est "participer", inscrire un participant
    if ($action === 'participate') {
        $id_participant = $_POST['id_participant'];
        $success = $model->participate($id_participant);  // Inscrire un participant
        
        if ($success) {
            $_SESSION['success'] = "Participant inscrit avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'inscription.";
        }
        header('Location: ../View/admin_participants.php');
        exit();
    }

    // Si l'action est "annuler", annuler la participation d'un participant
    if ($action === 'cancelParticipation') {
        $id_participant = $_POST['id_participant'];
        $success = $model->cancelParticipation($id_participant);  // Annuler la participation
        
        if ($success) {
            $_SESSION['success'] = "Participation annulée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'annulation.";
        }
        header('Location: ../View/admin_participants.php');
        exit();
    }
}
?>
