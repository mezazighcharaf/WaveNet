<?php
session_start();

require_once(__DIR__ . '/../models/Transport.php');
require_once(__DIR__ . '/../models/Utilisateur.php');
require_once(__DIR__ . '/../views/includes/config.php');

class TransportController {
    public function gererTransports() {
        error_log("[TransportController::gererTransports] Méthode appelée");
        
        // Vérifier l'authentification
        if (!isset($_SESSION['user_id']) || $_SESSION['user_niveau'] !== 'admin') {
            error_log("[TransportController::gererTransports] Accès refusé. user_id: " . 
                (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'non défini') . 
                ", user_niveau: " . (isset($_SESSION['user_niveau']) ? $_SESSION['user_niveau'] : 'non défini'));
            $_SESSION['error_message'] = "Vous devez être connecté en tant qu'administrateur pour accéder à cette page.";
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        
        // Rediriger vers la vue
        header('Location: /WaveNet/views/backoffice/gererTransport.php');
        exit;
    }
    
    /**
     * Méthode pour supprimer un transport
     */
    public function supprimerTransport() {
        /* Commenté pour déboguer
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = "Vous devez être connecté pour effectuer cette action.";
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        */
        
        // Ajout temporaire pour le débogage - simuler un utilisateur connecté
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['user_id'] = 1; // Utilisez un ID d'utilisateur valide dans votre base de données
        }
        
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_SESSION['error_messages'] = ["ID de transport non valide."];
            header('Location: /WaveNet/views/frontoffice/manageTransport.php');
            exit;
        }
        
        $idTransport = intval($_GET['id']);
        $db = connectDB();
        
        // Vérifier que le transport appartient bien à l'utilisateur
        $stmt = $db->prepare("SELECT id_utilisateur FROM TRANSPORT WHERE id_transport = ?");
        $stmt->execute([$idTransport]);
        $transport = $stmt->fetch();
        
        if (!$transport || $transport['id_utilisateur'] != $_SESSION['user_id']) {
            $_SESSION['error_messages'] = ["Vous n'êtes pas autorisé à supprimer ce transport."];
            header('Location: /WaveNet/views/frontoffice/manageTransport.php');
            exit;
        }
        
        // Supprimer le transport
        if (Transport::delete($db, $idTransport)) {
            $_SESSION['success_message'] = "Transport supprimé avec succès.";
        } else {
            $_SESSION['error_messages'] = ["Erreur lors de la suppression du transport."];
        }
        
        header('Location: /WaveNet/views/frontoffice/manageTransport.php');
        exit;
    }
    
    /**
     * Méthode pour modifier un transport
     */
    public function modifierTransport() {
        /* Commenté pour déboguer
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = "Vous devez être connecté pour effectuer cette action.";
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }
        */
        
        // Ajout temporaire pour le débogage - simuler un utilisateur connecté
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['user_id'] = 1; // Utilisez un ID d'utilisateur valide dans votre base de données
        }
        
        $erreurs = [];
        
        // Vérifier si c'est une requête POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération et validation des données
            $idTransport = isset($_POST['id_transport']) ? intval($_POST['id_transport']) : 0;
            $typeTransport = isset($_POST['type_transport']) ? trim($_POST['type_transport']) : '';
            $distanceParcourue = isset($_POST['distance_parcourue']) ? floatval($_POST['distance_parcourue']) : 0;
            $frequence = isset($_POST['frequence']) ? intval($_POST['frequence']) : 0;
            $dateDerniereUtilisation = !empty($_POST['date_derniere_utilisation']) ? $_POST['date_derniere_utilisation'] : date('Y-m-d');
            
            // Validation
            if (empty($typeTransport)) {
                $erreurs[] = "Le type de transport est obligatoire.";
            }
            
            if ($distanceParcourue <= 0) {
                $erreurs[] = "La distance doit être supérieure à zéro.";
            }
            
            if ($frequence <= 0) {
                $erreurs[] = "La fréquence doit être supérieure à zéro.";
            }
            
            if (empty($erreurs)) {
                $db = connectDB();
                
                // Vérifier que le transport appartient bien à l'utilisateur
                $stmt = $db->prepare("SELECT * FROM TRANSPORT WHERE id_transport = ?");
                $stmt->execute([$idTransport]);
                $transportData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$transportData || $transportData['id_utilisateur'] != $_SESSION['user_id']) {
                    $_SESSION['error_messages'] = ["Vous n'êtes pas autorisé à modifier ce transport."];
                    header('Location: /WaveNet/views/frontoffice/manageTransport.php');
                    exit;
                }
                
                // Récupérer l'eco-index pour le type de transport sélectionné
                $queryEcoIndex = $db->prepare("SELECT eco_index FROM TRANSPORT_TYPE WHERE nom = ?");
                $queryEcoIndex->execute([$typeTransport]);
                $ecoIndexData = $queryEcoIndex->fetch(PDO::FETCH_ASSOC);
                
                if ($ecoIndexData) {
                    $ecoIndex = floatval($ecoIndexData['eco_index']);
                } else {
                    // Si le type n'existe pas dans la table, utiliser une valeur par défaut
                    $ecoIndex = 5.0;
                }
                
                // Créer l'objet Transport et mettre à jour
                $transport = new Transport(
                    $transportData['id_utilisateur'],
                    $typeTransport,
                    $distanceParcourue,
                    $frequence,
                    $ecoIndex,
                    $dateDerniereUtilisation,
                    $idTransport
                );
                
                if ($transport->update($db)) {
                    $_SESSION['success_message'] = "Transport mis à jour avec succès.";
                    header('Location: /WaveNet/views/frontoffice/manageTransport.php');
                    exit;
                } else {
                    $erreurs[] = "Erreur lors de la mise à jour du transport.";
                }
            }
            
            // En cas d'erreurs
            $_SESSION['error_messages'] = $erreurs;
            header('Location: /WaveNet/views/frontoffice/manageTransport.php');
            exit;
        } else {
            // Si ce n'est pas une requête POST, rediriger vers la page de gestion
            header('Location: /WaveNet/views/frontoffice/manageTransport.php');
            exit;
        }
    }
}

// Routage des actions
if (isset($_GET['action'])) {
    $controller = new TransportController();
    $action = $_GET['action'];
    
    switch ($action) {
        case 'gererTransports':
            $controller->gererTransports();
            break;
        case 'supprimerTransport':
            $controller->supprimerTransport();
            break;
        case 'modifierTransport':
            $controller->modifierTransport();
            break;
        default:
            header('Location: /WaveNet/views/frontoffice/manageTransport.php');
            exit;
    }
}
