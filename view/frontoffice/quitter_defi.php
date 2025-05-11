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
    
    // Vérifier si le défi est complété
    $query = "SELECT Etape_En_Cours FROM utilisateur WHERE Id_Utilisateur = ? AND Defi_En_Cours = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $userId);
    $stmt->bindParam(2, $defiId);
    $stmt->execute();
    $userDefi = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Récupérer le nombre total d'étapes du défi
    $query = "SELECT COUNT(*) as total FROM etape WHERE Id_Defi = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $defiId);
    $stmt->execute();
    $totalEtapes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Si le défi est complété, rediriger avec un message d'erreur
    if ($userDefi && $userDefi['Etape_En_Cours'] >= $totalEtapes) {
        header('Location: defi.php?id=' . $defiId . '&error=Impossible de quitter un défi déjà complété');
        exit;
    }
    
    // Sinon, procéder à la sortie du défi
    $query = "UPDATE utilisateur SET Defi_En_Cours = NULL, Etape_En_Cours = 0 WHERE Id_Utilisateur = ? AND Defi_En_Cours = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $userId);
    $stmt->bindParam(2, $defiId);
    $stmt->execute();
    
    header('Location: defi.php?id=' . $defiId . '&message=Vous avez quitté ce défi');
    exit;
} catch (PDOException $e) {
    header('Location: defi.php?id=' . $defiId . '&error=Une erreur est survenue');
    exit;
}
?> 