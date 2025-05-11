<?php
session_start();
require_once __DIR__ . '/controller/EtapeController.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

// Récupérer les données
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['image']) || !isset($data['etape_id'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

// Décoder l'image base64
$imageData = $data['image'];
$imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
$imageData = str_replace(' ', '+', $imageData);
$imageData = base64_decode($imageData);

// Créer un nom de fichier unique
$filename = 'uploads/etapes/' . uniqid() . '_' . $_SESSION['user_id'] . '.jpg';

// Créer le dossier s'il n'existe pas
if (!file_exists('uploads/etapes')) {
    mkdir('uploads/etapes', 0777, true);
}

// Sauvegarder l'image
if (file_put_contents($filename, $imageData)) {
    // Mettre à jour la base de données
    $etapeController = new EtapeController();
    $result = $etapeController->validateEtape($data['etape_id'], $_SESSION['user_id'], $filename);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Étape validée avec succès',
            'points' => $result['points']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la validation de l\'étape']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde de l\'image']);
} 