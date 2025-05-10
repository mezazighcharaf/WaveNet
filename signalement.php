<?php
// Point d'entrée du module signalement
// Ce fichier agit comme un contrôleur frontal pour le module

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure l'adaptateur de configuration
require_once 'config/signalement_adapter.php';

// Fonction pour vérifier si un fichier existe avant de l'inclure
function includeFileIfExists($filePath) {
    if (file_exists($filePath)) {
        include $filePath;
        return true;
    } else {
        echo "<div class='alert alert-danger'>Fichier introuvable: " . htmlspecialchars($filePath) . "</div>";
        return false;
    }
}

// Inclure les modèles nécessaires
require_once 'gestion signalement/model/signalement.php';
require_once 'gestion signalement/model/intervention.php';

// Définir la page active pour le menu
$activePage = isset($_GET['controller']) && $_GET['controller'] == 'intervention' ? 'intervention_integrated' : 'signalement_integrated';
$pageTitle = isset($_GET['controller']) && $_GET['controller'] == 'intervention' ? 'Gestion des Interventions' : 'Gestion des Signalements';

// Inclure l'en-tête
require_once 'views/includes/header.php';

// Déterminer l'action à effectuer
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'signalement';

// Afficher les informations sur les chemins pour le débogage
echo '<div class="container mt-4">';
echo '<h1>' . $pageTitle . '</h1>';

// Charger le contrôleur approprié
if ($controller == 'signalement') {
    require_once 'gestion signalement/controller/signalementctrl.php';
    $ctrl = new SignalementC();
    
    // Exécuter l'action demandée
    switch ($action) {
        case 'add':
            // Logique pour ajouter un signalement
            $viewPath = 'gestion signalement/view/back office/addsignalement.php';
            includeFileIfExists($viewPath);
            break;
            
        case 'edit':
            // Logique pour éditer un signalement
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            if ($id) {
                $signalement = $ctrl->rechercher($id);
                $viewPath = 'gestion signalement/view/back office/modifiersignalement.php';
                includeFileIfExists($viewPath);
            } else {
                echo '<div class="alert alert-danger">ID de signalement non spécifié</div>';
                $viewPath = 'gestion signalement/view/back office/affichesignalement.php';
                includeFileIfExists($viewPath);
            }
            break;
            
        case 'delete':
            // Logique pour supprimer un signalement
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            if ($id && $ctrl->deleteSignalement($id)) {
                echo '<div class="alert alert-success">Signalement supprimé avec succès</div>';
            } else {
                echo '<div class="alert alert-danger">Erreur lors de la suppression</div>';
            }
            $signalements = $ctrl->afficherSignalement();
            $viewPath = 'gestion signalement/view/back office/affichesignalement.php';
            includeFileIfExists($viewPath);
            break;
            
        case 'list':
        default:
            // Afficher la liste des signalements
            $signalements = $ctrl->afficherSignalement();
            $viewPath = 'gestion signalement/view/back office/affichesignalement.php';
            includeFileIfExists($viewPath);
            break;
    }
} elseif ($controller == 'intervention') {
    // Charger le contrôleur d'intervention
    require_once 'gestion signalement/controller/interventionctrl.php';
    $ctrl = new InterventionC();
    
    // Exécuter l'action demandée
    switch ($action) {
        case 'add':
            // Logique pour ajouter une intervention
            $viewPath = 'gestion signalement/view/back office/ajouterintervention.php';
            includeFileIfExists($viewPath);
            break;
            
        case 'edit':
            // Logique pour éditer une intervention
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            if ($id) {
                $intervention = $ctrl->getInterventionById($id);
                $viewPath = 'gestion signalement/view/back office/modifierintervention.php';
                includeFileIfExists($viewPath);
            } else {
                echo '<div class="alert alert-danger">ID d\'intervention non spécifié</div>';
                $viewPath = 'gestion signalement/view/back office/afficherintervention.php';
                includeFileIfExists($viewPath);
            }
            break;
            
        case 'delete':
            // Logique pour supprimer une intervention
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            if ($id && $ctrl->deleteIntervention($id)) {
                echo '<div class="alert alert-success">Intervention supprimée avec succès</div>';
            } else {
                echo '<div class="alert alert-danger">Erreur lors de la suppression</div>';
            }
            $interventions = $ctrl->afficherIntervention();
            $viewPath = 'gestion signalement/view/back office/afficherintervention.php';
            includeFileIfExists($viewPath);
            break;
            
        case 'list':
        default:
            // Afficher la liste des interventions
            $interventions = $ctrl->afficherIntervention();
            $viewPath = 'gestion signalement/view/back office/afficherintervention.php';
            includeFileIfExists($viewPath);
            break;
    }
}

echo '</div>'; // Fin du container

// Inclure le pied de page
require_once 'views/includes/footer.php';
?> 