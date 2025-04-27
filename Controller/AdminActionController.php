<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../Model/EcoActionBackModel.php';

$model = new EcoActionBackModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add' || $action === 'update') {
        // Validation des champs obligatoires
        if (empty($_POST['nom']) || empty($_POST['description']) || empty($_POST['date']) || empty($_POST['statut']) || empty($_POST['points_verts']) || empty($_POST['categorie'])) {
            $_SESSION['error'] = "Tous les champs doivent être remplis.";
            header('Location: ../View/eco_actionsB.php');
            exit();
        }

        // Validation du nom de l'action (3 à 15 caractères)
        $nom = trim($_POST['nom']);
        if (strlen($nom) < 3 || strlen($nom) > 15) {
            $_SESSION['error'] = "Le nom de l'action doit être compris entre 3 et 15 caractères.";
            header('Location: ../View/eco_actionsB.php');
            exit();
        }

        // Validation des points verts (strictement supérieur à 0)
        $pointsVerts = intval($_POST['points_verts']);
        if ($pointsVerts <= 0) {
            $_SESSION['error'] = "Les points verts doivent être strictement supérieurs à 0.";
            header('Location: ../View/eco_actionsB.php');
            exit();
        }

        // Validation de la date (doit être supérieure à aujourd'hui)
        $currentDate = date('Y-m-d');
        $actionDate = $_POST['date']; // Le format attendu est 'Y-m-d'
        if ($actionDate <= $currentDate) {
            $_SESSION['error'] = "La date doit être supérieure à aujourd'hui.";
            header('Location: ../View/eco_actionsB.php');
            exit();
        }

        // Ajouter ou mettre à jour l'action
        if ($action === 'add') {
            $success = $model->addEcoAction(
                $nom,
                $_POST['description'],
                $actionDate,
                $_POST['statut'],
                $pointsVerts,
                $_POST['categorie']
            );

            if ($success) {
                $_SESSION['success'] = "Action ajoutée avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout.";
            }
        } else if ($action === 'update') {
            $success = $model->updateEcoAction(
                $_POST['id_action'],
                $nom,
                $_POST['description'],
                $actionDate,
                $_POST['statut'],
                $pointsVerts,
                $_POST['categorie']
            );

            if ($success) {
                $_SESSION['success'] = "Action mise à jour avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour.";
            }
        }

        header('Location: ../View/eco_actionsB.php');
        exit();
    }

    if ($action === 'delete') {
        $success = $model->deleteEcoAction($_POST['id_action']);

        if ($success) {
            $_SESSION['success'] = "Action supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression.";
        }
        header('Location: ../View/eco_actionsB.php');
        exit();
    }
}
?>
