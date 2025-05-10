<?php
// Point d'entrée pour le backoffice du module signalement
session_start();

// Vérification de l'authentification et des droits d'administration
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_niveau']) || $_SESSION['user_niveau'] !== 'admin') {
    header('Location: /WaveNet/views/frontoffice/login.php');
    exit;
}

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

// Détermine quel module afficher (signalement ou intervention)
$module = isset($_GET['module']) ? $_GET['module'] : 'signalement';

// Charge les modèles nécessaires
require_once 'gestion signalement/model/signalement.php';
require_once 'gestion signalement/model/intervention.php';

// Configuration des titres et chemins selon le module
if ($module == 'signalement') {
    $pageTitle = 'Gestion des Signalements';
    require_once 'gestion signalement/controller/signalementctrl.php';
    $controller = new SignalementC();
    
    // Gestion des actions (affichage, modification, suppression)
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    if ($action == 'edit' && isset($_GET['id'])) {
        // Édition d'un signalement
        $viewPath = 'gestion signalement/view/back office/modifiersignalement.php';
    } elseif ($action == 'add') {
        // Ajout d'un signalement
        $viewPath = 'gestion signalement/view/back office/addsignalement.php';
    } else {
        // Liste des signalements (par défaut)
        $viewPath = 'gestion signalement/view/back office/affichesignalement.php';
        // Si le fichier affichesignalement.php n'existe pas, utiliser index.php
        if (!file_exists($viewPath)) {
            $viewPath = 'gestion signalement/view/back office/index.php';
        }
    }
} elseif ($module == 'intervention') {
    $pageTitle = 'Gestion des Interventions';
    require_once 'gestion signalement/controller/interventionctrl.php';
    $controller = new InterventionC();
    
    // Gestion des actions pour les interventions
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    if ($action == 'edit' && isset($_GET['id'])) {
        // Édition d'une intervention
        $viewPath = 'gestion signalement/view/back office/modifierintervention.php';
    } elseif ($action == 'add') {
        // Ajout d'une intervention
        $viewPath = 'gestion signalement/view/back office/ajouterintervention.php';
    } else {
        // Liste des interventions (par défaut)
        $viewPath = 'gestion signalement/view/back office/afficherintervention.php';
    }
} else {
    die("Module non reconnu");
}

// Début de la structure HTML
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo $pageTitle; ?> - Backoffice WaveNet</title>
  
  <!-- Styles principaux de WaveNet -->
  <link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css" />
  <link rel="stylesheet" href="/WaveNet/views/assets/css/admin-dashboard.css" />
  <link rel="stylesheet" href="/WaveNet/views/assets/css/backoffice11.css" />
  
  <!-- Styles d'intégration du module signalement -->
  <link rel="stylesheet" href="/WaveNet/views/assets/css/backoffice_integration.css" />
  
  <!-- Styles du module signalement (conditionnel) -->
  <?php if (file_exists('gestion signalement/view/back office/css/backoffice.css')): ?>
  <link rel="stylesheet" href="/WaveNet/gestion signalement/view/back office/css/backoffice.css" />
  <?php endif; ?>
  
  <!-- Intégration FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- jQuery et Bootstrap pour les fonctionnalités -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Chart.js pour les graphiques -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="logo">
      <h1>WaveNet</h1>
    </div>
    <nav class="sidebar-nav">
      <ul>
        <li><a href="/WaveNet/views/backoffice/index.php">Dashboard</a></li>
        <li><a href="/WaveNet/views/backoffice/listeUtilisateurs.php">Utilisateurs</a></li>
        <li><a href="/WaveNet/views/backoffice/defis.php">Défis</a></li>
        <li><a href="/WaveNet/views/backoffice/quartiers.php">Quartiers</a></li>
        <li><a href="/WaveNet/backoffice_signalement.php?module=signalement" <?php echo $module == 'signalement' ? 'class="active"' : ''; ?>>Signalements</a></li>
        <li><a href="/WaveNet/backoffice_signalement.php?module=intervention" <?php echo $module == 'intervention' ? 'class="active"' : ''; ?>>Interventions</a></li>
        <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php">Accueil frontoffice</a></li>
      </ul>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <header class="content-header">
      <h1><?php echo $pageTitle; ?></h1>
      <div class="user-info">
        <span><?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?> (Admin)</span>
        <a href="/WaveNet/controller/UserController.php?action=logout" class="logout-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
      </div>
    </header>

    <!-- Contenu spécifique du module -->
    <div class="module-content">
      <?php 
      // Inclure la vue appropriée
      includeFileIfExists($viewPath);
      ?>
    </div>
  </main>

  <!-- Scripts spécifiques du module signalement (si nécessaire) -->
  <?php if (file_exists('gestion signalement/view/back office/js/sb-admin-2.min.js')): ?>
  <script src="/WaveNet/gestion signalement/view/back office/js/sb-admin-2.min.js"></script>
  <?php endif; ?>
</body>
</html> 