<?php
require_once 'TransportController.php';
require_once 'UserController.php';

$action = $_GET['action'] ?? '';

if (strpos($action, 'ajouterTransport') === 0) {
    $userController = new UserController();
    $userController->ajouterTransport();
} elseif ($action === 'modifierTransport') {
    $transportController = new TransportController();
    $transportController->modifierTransport();
} elseif ($action === 'supprimerTransport') {
    $transportController = new TransportController();
    $transportController->supprimerTransport();
} elseif ($action === 'gererTransports') {
    // Ajout du support pour gererTransports
    $transportController = new TransportController();
    $transportController->gererTransports();
} else {
    // Action non reconnue
    header('Location: /WaveNet/views/frontoffice/userDashboard.php');
    exit;
}
?> 