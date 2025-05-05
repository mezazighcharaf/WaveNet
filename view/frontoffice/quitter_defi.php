<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === 'demo_user') {
    // Rediriger vers la page de connexion
    header('Location: login.php');
    exit;
}

// Vérifier si un ID de défi est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: defis.php');
    exit;
}

$defiId = (int)$_GET['id'];
$userId = $_SESSION['user_id'];

// Inclure la connexion à la base de données
require_once __DIR__ . '/../../model/Database.php';

// Connexion à la base de données
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Vérifier si l'utilisateur participe actuellement à ce défi
$query = "SELECT Defi_En_Cours FROM utilisateur WHERE Id_Utilisateur = ? AND Defi_En_Cours = ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $userId);
$stmt->bindParam(2, $defiId);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $_SESSION['message'] = "Vous ne participez pas à ce défi actuellement.";
    $_SESSION['message_type'] = "error";
    header('Location: defi.php?id=' . $defiId);
    exit;
}

// Mettre à jour la base de données pour quitter le défi
$query = "UPDATE utilisateur SET Defi_En_Cours = NULL, Etape_En_Cours = NULL WHERE Id_Utilisateur = ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $userId);

if ($stmt->execute()) {
    $_SESSION['message'] = "Vous avez quitté le défi avec succès.";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Une erreur est survenue lors de la tentative de quitter le défi.";
    $_SESSION['message_type'] = "error";
}

// Rediriger vers la page du défi
header('Location: defi.php?id=' . $defiId);
exit;
?> 