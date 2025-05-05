<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === 'demo_user') {
    echo json_encode(['success' => false, 'message' => "Utilisateur non connecté"]);
    exit;
}

// Vérifier si les données nécessaires sont fournies
if (!isset($_POST['etape']) || !isset($_POST['defi_id'])) {
    echo json_encode(['success' => false, 'message' => "Données manquantes"]);
    exit;
}

$etape = (int)$_POST['etape'];
$defiId = (int)$_POST['defi_id'];
$userId = $_SESSION['user_id'];
$pointsGagnes = 0;

// Inclure la connexion à la base de données
require_once __DIR__ . '/../../model/Database.php';

try {
    // Créer une instance de la classe Database pour obtenir une connexion
    $database = new Database();
    $db = $database->getConnection();
    
    // Vérifier si l'utilisateur participe bien à ce défi
    $query = "SELECT Defi_En_Cours, Etape_En_Cours FROM utilisateur WHERE Id_Utilisateur = ? AND Defi_En_Cours = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $userId);
    $stmt->bindParam(2, $defiId);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => "Vous ne participez pas à ce défi"]);
        exit;
    }
    
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    $etapePrecedente = isset($userInfo['Etape_En_Cours']) ? (int)$userInfo['Etape_En_Cours'] : 0;
    
    // Log pour le débogage
    error_log("Sauvegarde étape: Utilisateur $userId, Défi $defiId, Étape $etape, Précédente $etapePrecedente");
    
    // Calculer les points si on a avancé à une nouvelle étape
    if ($etape > $etapePrecedente) {
        // Récupérer les points de l'étape actuelle
        require_once __DIR__ . '/../../controller/EtapeController.php';
        $etapeController = new EtapeController();
        $etapes = $etapeController->getEtapesByDefi($defiId);
        
        // L'indice de l'étape dans le tableau est etape-1 (car étape 0 = départ)
        if ($etape > 0 && $etape <= count($etapes)) {
            $etapeIndex = $etape - 1;
            if (isset($etapes[$etapeIndex]) && isset($etapes[$etapeIndex]['Points_Bonus'])) {
                $pointsGagnes = (int)$etapes[$etapeIndex]['Points_Bonus'];
            }
        }
        
        // Si c'est la dernière étape, on ajoute les points du défi entier
        if ($etape == count($etapes) + 1) {
            // Récupérer les points du défi
            $query = "SELECT Points_verts FROM defi WHERE Id_Defi = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $defiId);
            $stmt->execute();
            $defiInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($defiInfo && isset($defiInfo['Points_verts'])) {
                $pointsGagnes += (int)$defiInfo['Points_verts'];
            }
        }
        
        // Mettre à jour les points de l'utilisateur s'il y a des points à ajouter
        if ($pointsGagnes > 0) {
            $query = "UPDATE utilisateur SET Points_verts = Points_verts + ? WHERE Id_Utilisateur = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $pointsGagnes);
            $stmt->bindParam(2, $userId);
            $stmt->execute();
            
            // Mettre à jour les points dans la session
            if (isset($_SESSION['points'])) {
                $_SESSION['points'] += $pointsGagnes;
            } else {
                // Récupérer le total des points de l'utilisateur
                $query = "SELECT Points_verts FROM utilisateur WHERE Id_Utilisateur = ?";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $userId);
                $stmt->execute();
                $userPoints = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($userPoints && isset($userPoints['Points_verts'])) {
                    $_SESSION['points'] = (int)$userPoints['Points_verts'];
                }
            }
        }
    }
    
    // S'assurer que l'on ne recule pas dans les étapes (seulement avancer)
    if ($etape < $etapePrecedente) {
        $etape = $etapePrecedente;
    }
    
    // Mettre à jour l'étape en cours pour l'utilisateur - s'assurer que la requête spécifie bien le défi
    $query = "UPDATE utilisateur SET Etape_En_Cours = ? WHERE Id_Utilisateur = ? AND Defi_En_Cours = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $etape);
    $stmt->bindParam(2, $userId);
    $stmt->bindParam(3, $defiId);
    
    $result = $stmt->execute();
    error_log("Mise à jour étape: " . ($result ? "Succès" : "Échec") . " pour utilisateur $userId, défi $defiId, étape $etape");
    
    if ($result) {
        // Si c'est la dernière étape, marquer le défi comme terminé dans la table participation
        if ($etape >= count($etapes) + 1) { // Dernière étape (après toutes les étapes du défi)
            $query = "INSERT INTO participation (Id_Utilisateur, Id_Defi, Date_Participation) 
                      VALUES (?, ?, NOW()) 
                      ON DUPLICATE KEY UPDATE Date_Participation = NOW()";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $userId);
            $stmt->bindParam(2, $defiId);
            $success = $stmt->execute();
            error_log("Enregistrement de participation: " . ($success ? "Succès" : "Échec"));
        }
        
        echo json_encode([
            'success' => true, 
            'message' => "Progression sauvegardée", 
            'points' => $pointsGagnes,
            'etape' => $etape
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => "Erreur lors de la sauvegarde"]);
    }
} catch (PDOException $e) {
    error_log("Erreur PDO: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => "Erreur: " . $e->getMessage()]);
}
?> 