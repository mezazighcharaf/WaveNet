<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

require_once __DIR__ . '/../../model/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $query = "SELECT Points_verts FROM utilisateur WHERE Id_Utilisateur = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && isset($user['Points_verts'])) {
        echo json_encode(['success' => true, 'points' => (int)$user['Points_verts']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
