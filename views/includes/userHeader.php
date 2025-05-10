<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// DEBUG - SUPPRIMER APRÈS TEST
echo "<!-- userHeader.php chargé - Version: " . date('Y-m-d H:i:s') . " -->";

// Données utilisateur
$userId = $_SESSION['user_id'] ?? null;
$userNom = $_SESSION['user_nom'] ?? '';
$userPrenom = $_SESSION['user_prenom'] ?? 'Utilisateur';
$userNiveau = $_SESSION['user_niveau'] ?? 'client'; // Ou user_role selon votre session
$isAdmin = ($userNiveau === 'admin');

// Page active pour la navigation
$currentScript = basename($_SERVER['PHP_SELF']);
$activePage = $activePage ?? ''; // Assure que $activePage existe
?>
<head>
    <link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .user-header {
            background-color: var(--dark-green, #2E7D32);
            padding: 0.8rem 2rem;
            box-shadow: var(--shadow-sm, 0 2px 4px rgba(0,0,0,0.1));
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .user-header .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        .user-header .logo h1 {
            color: var(--white, #fff);
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
        }
        .user-header .nav-links {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            margin: 0;
            padding: 0;
        }
        .user-header .nav-links a {
            color: var(--gray-100, #f5f5f5);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            padding: 0.5rem 0;
            position: relative;
            transition: color var(--transition-speed, 0.2s);
        }
        .user-header .nav-links a:hover,
        .user-header .nav-links a.active {
            color: var(--white, #fff);
        }
        .user-header .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--accent-green, #66BB6A);
            transition: width var(--transition-speed, 0.2s) ease-out;
        }
        .user-header .nav-links a.active::after,
        .user-header .nav-links a:hover::after {
            width: 100%;
        }
        .user-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
        }
        .user-profile-dropdown {
            position: relative;
        }
        .user-profile-button {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--white, #fff);
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius, 4px);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color var(--transition-speed, 0.2s);
        }
        .user-profile-button:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .user-profile-button i {
            font-size: 0.9em;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 0px); /* Réduit l'espace entre le bouton et le menu */
            right: 0;
            background-color: var(--white, #fff);
            border-radius: var(--border-radius, 4px);
            box-shadow: var(--shadow-md, 0 4px 12px rgba(0,0,0,0.15));
            min-width: 220px;
            z-index: 1100;
            padding: 0.5rem 0;
            overflow: hidden;
        }
        /* Ajout d'un padding supérieur invisible pour éviter les "trous" entre le bouton et le menu */
        .dropdown-menu::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 0;
            right: 0;
            height: 10px;
            background: transparent;
        }
        /* Active le menu au hover du conteneur du dropdown, pas seulement le bouton */
        .user-profile-dropdown:hover .dropdown-menu {
            display: block;
        }
        /* S'assurer que le menu reste visible quand on le survole */
        .dropdown-menu:hover {
            display: block;
        }
        .dropdown-menu.show {
            display: block;
        }
        .dropdown-menu a, .dropdown-menu .dropdown-header {
            display: block;
            padding: 0.75rem 1.5rem;
            color: var(--text-color, #333);
            text-decoration: none;
            font-size: 0.95rem;
            white-space: nowrap;
            transition: background-color var(--transition-speed, 0.2s), color var(--transition-speed, 0.2s);
        }
        .dropdown-menu a i {
            margin-right: 0.8rem;
            width: 16px; /* Aligner les icônes */
            text-align: center;
            color: var(--gray-500, #777);
        }
        .dropdown-menu a:hover {
            background-color: var(--gray-100, #f5f5f5);
            color: var(--dark-green, #2E7D32);
        }
         .dropdown-menu a:hover i {
             color: var(--accent-green, #66BB6A);
         }
        .dropdown-divider {
            height: 1px;
            background-color: var(--gray-200, #eee);
            margin: 0.5rem 0;
        }
        .dropdown-header {
            font-weight: 600;
            color: var(--dark-green, #2E7D32);
            font-size: 0.9rem;
            padding-bottom: 0.5rem;
            padding-top: 0.75rem;
        }
        /* Responsive */
        @media (max-width: 992px) {
            .user-header .nav-links {
                display: none; /* Cacher liens principaux sur petit écran pour l'instant */
            }
             .user-header .nav-container {
                padding: 0.8rem 1rem;
             }
        }
    </style>
</head>
<header class="main-header user-header">
  <div class="nav-container">
    <div class="logo">
      <a href="/WaveNet/views/frontoffice/userDashboard.php" style="text-decoration:none; color:inherit;"><h1>WaveNet</h1></a>
    </div>
    
    <nav>
      <ul class="nav-links">
        <?php if (!$isAdmin): ?>
            <li><a href="/WaveNet/views/frontoffice/userDashboard.php" class="<?php echo ($activePage === 'dashboard') ? 'active' : ''; ?>">Tableau de bord</a></li>
            <li><a href="/WaveNet/views/frontoffice/defis.php" class="<?php echo ($activePage === 'defis') ? 'active' : ''; ?>">Défis</a></li>
            <li><a href="/WaveNet/views/frontoffice/activites.php" class="<?php echo ($activePage === 'activites') ? 'active' : ''; ?>">Activités</a></li>
            <li><a href="/WaveNet/views/frontoffice/manageTransport.php" class="<?php echo ($activePage === 'transport') ? 'active' : ''; ?>">Transports</a></li>
            <li><a href="/WaveNet/views/frontoffice/addSignalement.php" class="<?php echo ($activePage === 'signalement') ? 'active' : ''; ?>">Signalements</a></li>
            <li><a href="/WaveNet/views/frontoffice/account_activity.php" class="<?php echo ($activePage === 'account_activity') ? 'active' : ''; ?>">Activité du compte</a></li>
        <?php else: ?>
             <li><a href="/WaveNet/views/backoffice/index.php" class="<?php echo ($currentScript === 'index.php') ? 'active' : ''; ?>">Dashboard Admin</a></li>
             <li><a href="/WaveNet/views/backoffice/listeUtilisateurs.php" class="<?php echo ($currentScript === 'listeUtilisateurs.php') ? 'active' : ''; ?>">Utilisateurs</a></li>
             <li><a href="/WaveNet/views/backoffice/defis.php" class="<?php echo ($currentScript === 'defis.php') ? 'active' : ''; ?>">Défis</a></li>
             <li><a href="/WaveNet/views/backoffice/quartiers.php" class="<?php echo ($currentScript === 'quartiers.php') ? 'active' : ''; ?>">Quartiers</a></li>
             <li><a href="/WaveNet/views/backoffice/Gquartier.php" class="<?php echo ($currentScript === 'Gquartier.php') ? 'active' : ''; ?>">Gestion Quartiers</a></li>
             <li><a href="/WaveNet/views/backoffice/backinfra.php" class="<?php echo ($currentScript === 'backinfra.php') ? 'active' : ''; ?>">Gestion Infrastructures</a></li>
             <li><a href="/WaveNet/views/backoffice/gsignalement.php" class="<?php echo ($currentScript === 'gsignalement.php') ? 'active' : ''; ?>">Gestion Signalements</a></li>
             <li><a href="/WaveNet/views/backoffice/afficherintervention.php" class="<?php echo ($activePage === 'intervention') ? 'active' : ''; ?>">Interventions</a></li>
             <li><a href="/WaveNet/views/frontoffice/account_activity.php" class="<?php echo ($activePage === 'account_activity') ? 'active' : ''; ?>">Activité du compte</a></li>
        <?php endif; ?>
      </ul>
    </nav>
    
    <div class="user-actions">
        <div class="user-profile-dropdown">
            <button class="user-profile-button">
                <i class="fas fa-user-circle"></i> 
                <span><?php echo htmlspecialchars($userPrenom); ?></span>
                <i class="fas fa-caret-down"></i>
            </button>
            <div class="dropdown-menu">
                <?php if ($isAdmin): ?>
                    <a href="/WaveNet/views/backoffice/index.php"><i class="fas fa-tachometer-alt"></i>Tableau de bord Admin</a>
                    <a href="/WaveNet/views/frontoffice/userDashboard.php"><i class="fas fa-user"></i>Voir profil Client</a>
                     <div class="dropdown-divider"></div>
                    <div class="dropdown-header">Administration</div>
                    <a href="/WaveNet/views/backoffice/listeUtilisateurs.php"><i class="fas fa-users-cog"></i>Gestion Utilisateurs</a>
                    <a href="/WaveNet/views/backoffice/defis.php"><i class="fas fa-flag"></i>Gestion Défis</a>
                    <a href="/WaveNet/views/backoffice/quartiers.php"><i class="fas fa-map-marker-alt"></i>Gestion Quartiers</a>
                    <a href="/WaveNet/views/backoffice/Gquartier.php"><i class="fas fa-map-marker-alt"></i>Gestion Quartiers</a>
                    <a href="/WaveNet/views/backoffice/backinfra.php"><i class="fas fa-cogs"></i>Gestion Infrastructures</a>
                    <a href="/WaveNet/views/backoffice/gsignalement.php"><i class="fas fa-exclamation-triangle"></i>Gestion Signalements</a>
                    <a href="/WaveNet/views/backoffice/afficherintervention.php"><i class="fas fa-tools"></i>Gestion Interventions</a>
                     <div class="dropdown-divider"></div>
                <?php else: ?>
                    <a href="/WaveNet/views/frontoffice/userDashboard.php"><i class="fas fa-tachometer-alt"></i>Tableau de bord</a>
                    <a href="/WaveNet/views/frontoffice/editProfile.php"><i class="fas fa-user-edit"></i>Modifier mon profil</a>
                    <a href="/WaveNet/views/frontoffice/viewSignalements.php"><i class="fas fa-exclamation-triangle"></i>Mes signalements</a>
                    <a href="/WaveNet/views/frontoffice/signalement.php"><i class="fas fa-plus-circle"></i>Ajouter un signalement</a>
                    <a href="/WaveNet/views/frontoffice/account_activity.php"><i class="fas fa-history"></i>Activité du compte</a>
                    <a href="/WaveNet/controller/UserController.php?action=gerer2FA"><i class="fas fa-shield-alt"></i>Sécurité du compte</a>
                    <div class="dropdown-divider"></div>
                    <a href="/WaveNet/views/frontoffice/account_activity.php#rgpd"><i class="fas fa-download"></i>Exporter mes données (RGPD)</a>
                <?php endif; ?>
                <a href="/WaveNet/controller/UserController.php?action=logout"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
            </div>
        </div>
    </div>
  </div>
</header>

<!-- Bandeau d'impersonation pour les administrateurs -->
<?php if (isset($_SESSION['impersonation_active']) && $_SESSION['impersonation_active'] === true): ?>
<div style="background-color: #dc3545; color: white; padding: 10px; text-align: center; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
    <div style="flex: 1;">
        <i class="fas fa-user-secret"></i> 
        Vous êtes connecté en tant que <?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?> 
        (Administration)
    </div>
    <a href="/WaveNet/controller/UserController.php?action=stopImpersonation" 
       style="background-color: white; color: #dc3545; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-weight: bold;">
        <i class="fas fa-sign-out-alt"></i> Revenir à mon compte
    </a>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner les éléments du dropdown
    const dropdownButton = document.querySelector('.user-profile-button');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    
    // Ajouter un écouteur d'événement au clic sur le bouton
    dropdownButton.addEventListener('click', function(e) {
        e.preventDefault();
        dropdownMenu.classList.toggle('show');
    });
    
    // Fermer le dropdown quand on clique ailleurs sur la page
    document.addEventListener('click', function(e) {
        if (!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.remove('show');
        }
    });
    
    // Empêcher la propagation des clics à l'intérieur du menu
    dropdownMenu.addEventListener('click', function(e) {
        // Ne pas empêcher les clics sur les liens
        if (e.target.tagName !== 'A' && !e.target.closest('a')) {
            e.stopPropagation();
        }
    });
});
</script>
