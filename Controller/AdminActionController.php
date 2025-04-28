<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../Model/EcoActionBackModel.php';

$model = new EcoActionBackModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $errors = []; // Tableau pour stocker les erreurs
    $formData = $_POST; // Sauvegarder les données saisies

    if ($action === 'add' || $action === 'update') {
        // Validation des champs obligatoires
        if (empty($_POST['nom'])) $errors['nom'] = "Le nom de l'action est requis.";
        if (empty($_POST['description'])) $errors['description'] = "La description est requise.";
        if (empty($_POST['date'])) $errors['date'] = "La date est requise.";
        if (empty($_POST['statut'])) $errors['statut'] = "Le statut est requis.";
        if (empty($_POST['points_verts'])) $errors['points_verts'] = "Les points verts sont requis.";
        if (empty($_POST['categorie'])) $errors['categorie'] = "La catégorie est requise.";

        // Validation du nom de l'action (3 à 15 caractères)
        $nom = trim($_POST['nom']);
        if (strlen($nom) < 3 || strlen($nom) > 15) {
            $errors['nom'] = "Le nom de l'action doit être compris entre 3 et 15 caractères.";
        }

        // Validation des points verts (strictement supérieur à 0)
        $pointsVerts = intval($_POST['points_verts']);
        if ($pointsVerts <= 0) {
            $errors['points_verts'] = "Les points verts doivent être strictement supérieurs à 0.";
        }

        // Validation de la date (doit être supérieure à aujourd'hui)
        $currentDate = date('Y-m-d');
        $actionDate = $_POST['date'];
        if ($actionDate <= $currentDate) {
            $errors['date'] = "La date doit être supérieure à aujourd'hui.";
        }

        // Si aucune erreur, procéder à l'ajout ou la mise à jour
        if (empty($errors)) {
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
        } else {
            // Stocker les erreurs et les données saisies
            $_SESSION['errors'] = $errors;
            $_SESSION['formData'] = $formData;
        }

        // Rediriger vers la même page pour afficher les erreurs
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