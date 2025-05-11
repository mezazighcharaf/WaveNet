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

// Vérifier si le défi existe et est actif
$query = "SELECT * FROM defi WHERE Id_Defi = ? AND Statut_D = 'Actif'";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $defiId);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $_SESSION['message'] = "Ce défi n'est pas disponible actuellement.";
    $_SESSION['message_type'] = "error";
    header('Location: defi.php?id=' . $defiId);
    exit;
}

// Vérifier si l'utilisateur participe déjà à un autre défi
$query = "SELECT Defi_En_Cours FROM utilisateur WHERE Id_Utilisateur = ? AND Defi_En_Cours IS NOT NULL";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $userId);
$stmt->execute();

// Définir le défi en cours et initialiser l'étape à 0
$query = "UPDATE utilisateur SET Defi_En_Cours = ?, Etape_En_Cours = 0 WHERE Id_Utilisateur = ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $defiId);
$stmt->bindParam(2, $userId);

if ($stmt->execute()) {
    $_SESSION['message'] = "Bienvenue dans le défi ! Suivez les étapes pour le compléter.";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Une erreur est survenue lors de l'inscription au défi.";
    $_SESSION['message_type'] = "error";
}

// Rediriger vers la page du défi
header('Location: defi.php?id=' . $defiId);
exit;
?> 