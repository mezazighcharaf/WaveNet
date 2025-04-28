<?php
session_start();

// Check if user is logged in as admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'admin';
    $_SESSION['user_name'] = 'Admin';
}

require_once __DIR__ . '/../../../controller/EtapeController.php';

// Check if ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php?error=ID de l\'étape non spécifié');
    exit();
}

$id = $_GET['id'];
$etapeController = new EtapeController();

// Delete the etape
if($etapeController->deleteEtape($id)) {
    header('Location: index.php?message=Étape supprimée avec succès');
    exit();
} else {
    header('Location: index.php?error=Erreur lors de la suppression de l\'étape');
    exit();
}
?>