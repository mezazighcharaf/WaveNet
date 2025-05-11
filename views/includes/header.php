<!DOCTYPE html>
<html lang="fr">
<?php
// Initialisation des variables s'ils ne sont pas définis
if (!isset($pageTitle)) $pageTitle = "WaveNet";
if (!isset($activePage)) $activePage = "";
?>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo $pageTitle; ?></title>
  <link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <?php if (isset($additionalCss)): ?>
    <?php echo $additionalCss; ?>
  <?php endif; ?>
  <style>
    /* Ajustement de la position de la navbar */
    .navbar {
      margin-top: -10px; /* Remonter la navbar */
      padding: 0.5rem 1rem;
    }
    .navbar-dark {
      background-color: var(--dark-green) !important;
    }
    .navbar-brand {
      display: flex;
      align-items: center;
      font-weight: 600;
      color: var(--white);
      text-decoration: none;
    }
    .navbar-toggler {
      border: none;
      background-color: rgba(255, 255, 255, 0.1);
      padding: 0.5rem;
      border-radius: var(--border-radius-sm);
    }
    .navbar-toggler-icon {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
    .navbar-nav {
      display: flex;
      flex-direction: column;
    }
    .nav-link {
      color: rgba(255, 255, 255, 0.8);
      padding: 0.5rem 1rem;
      text-decoration: none;
      transition: color var(--transition-speed);
    }
    .nav-link:hover, .nav-link.active {
      color: var(--white);
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: var(--border-radius-sm);
    }
    .dropdown-menu {
      background-color: var(--white);
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-md);
      padding: 0.5rem 0;
    }
    .dropdown-item {
      padding: 0.5rem 1.5rem;
      color: var(--text-color);
      text-decoration: none;
    }
    .dropdown-item:hover {
      background-color: var(--light-green);
    }
    .btn-outline-light {
      color: var(--white);
      border: 1px solid var(--white);
      border-radius: var(--border-radius);
      padding: 0.375rem 0.75rem;
      text-decoration: none;
      transition: all var(--transition-speed);
    }
    .btn-outline-light:hover {
      background-color: var(--white);
      color: var(--dark-green);
    }
    .btn-success {
      background-color: var(--accent-green);
      border: 1px solid var(--accent-green);
      color: var(--white);
      border-radius: var(--border-radius);
      padding: 0.375rem 0.75rem;
      text-decoration: none;
      transition: all var(--transition-speed);
    }
    .btn-success:hover {
      background-color: #3e8e41;
      border-color: #3e8e41;
      transform: translateY(-3px);
      box-shadow: var(--shadow-md);
    }
    .btn-outline-success {
      color: var(--accent-green);
      border: 1px solid var(--accent-green);
      background-color: transparent;
      border-radius: var(--border-radius);
      padding: 0.375rem 0.75rem;
      text-decoration: none;
      transition: all var(--transition-speed);
    }
    .btn-outline-success:hover {
      background-color: var(--accent-green);
      color: var(--white);
      transform: translateY(-3px);
      box-shadow: var(--shadow-md);
    }
    
    /* Media queries pour la responsivité */
    @media (min-width: 992px) {
      .navbar-expand-lg .navbar-nav {
        flex-direction: row;
      }
      .navbar-expand-lg .navbar-collapse {
        display: flex !important;
      }
      .navbar-expand-lg .navbar-toggler {
        display: none;
      }
    }
    @media (max-width: 991.98px) {
      .collapse:not(.show) {
        display: none;
      }
    }
  </style>

</head>
<body>
  <!-- Barre de navigation moderne -->
  <header class="main-header">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" 
                aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link <?php echo $activePage == 'accueil' ? 'active' : ''; ?>" 
                 href="/WaveNet/index.php">Accueil</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo $activePage == 'about' ? 'active' : ''; ?>" 
                 href="/WaveNet/views/frontoffice/about.php">À propos</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo $activePage == 'quartiers' ? 'active' : ''; ?>" 
                 href="/WaveNet/views/frontoffice/frontquartier.php">Quartiers</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo $activePage == 'infrastructures' ? 'active' : ''; ?>" 
                 href="/WaveNet/views/frontoffice/frontinfra.php">Infrastructures</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo $activePage == 'signalement' ? 'active' : ''; ?>" 
                 href="/WaveNet/views/frontoffice/addSignalement.php">Signalement</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo $activePage == 'interventions' ? 'active' : ''; ?>" 
                 href="/WaveNet/views/frontoffice/interventions.php">Interventions</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo $activePage == 'recompenses' ? 'active' : ''; ?>" 
                 href="/WaveNet/views/frontoffice/recompenses.php">Récompenses</a>
            </li>
           
            <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
              <a class="nav-link <?php echo $activePage == 'dashboard' ? 'active' : ''; ?>" 
                 href="/WaveNet/views/frontoffice/userDashboard.php">Tableau de bord</a>
            </li>
            <?php if (isset($_SESSION['user_niveau']) && $_SESSION['user_niveau'] === 'admin'): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" 
                 data-bs-toggle="dropdown" aria-expanded="false">
                Administration
              </a>
              <ul class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                <li><a class="dropdown-item" href="/WaveNet/views/backoffice/index.php">Tableau de bord Admin</a></li>
                <li><a class="dropdown-item" href="/WaveNet/views/backoffice/listeUtilisateurs.php">Gestion des utilisateurs</a></li>
                <li><a class="dropdown-item" href="/WaveNet/views/backoffice/gestionDefis.php">Gestion des défis</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/WaveNet/views/backoffice/statistiques.php">Statistiques</a></li>
              </ul>
            </li>
            <?php endif; ?>
            <?php endif; ?>
          </ul>
          
          <div class="d-flex">
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="dropdown">
              <button class="btn btn-outline-success dropdown-toggle" type="button" id="dropdownMenuUser" 
                      data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle me-1"></i>
                <?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?>
              </button>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuUser">
                <li><a class="dropdown-item" href="/WaveNet/views/frontoffice/userDashboard.php">
                  <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord</a></li>
                <li><a class="dropdown-item" href="/WaveNet/views/frontoffice/editProfile.php">
                  <i class="fas fa-user-edit me-2"></i>Modifier mon profil</a></li>
                <li><a class="dropdown-item" href="/WaveNet/controller/UserController.php?action=gerer2FA">
                  <i class="fas fa-shield-alt me-2"></i>Sécurité du compte</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/WaveNet/controller/UserController.php?action=logout">
                  <i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
              </ul>
            </div>
            <?php else: ?>
            <a href="/WaveNet/views/frontoffice/login.php" class="btn btn-outline-light me-2">Connexion</a>
            <a href="/WaveNet/views/frontoffice/register.php" class="btn btn-success">Inscription</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <!-- Script pour activer le dropdown sans Bootstrap -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Vérifier si Bootstrap n'est pas chargé
      if (typeof bootstrap === 'undefined') {
        const dropdownToggle = document.querySelectorAll('.dropdown-toggle');
        
        dropdownToggle.forEach(toggle => {
          toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentNode;
            const menu = parent.querySelector('.dropdown-menu');
            
            if (menu.style.display === 'block') {
              menu.style.display = 'none';
            } else {
              // Fermer tous les autres menus
              document.querySelectorAll('.dropdown-menu').forEach(m => {
                if (m !== menu) m.style.display = 'none';
              });
              menu.style.display = 'block';
            }
          });
        });
        
        // Fermer les menus quand on clique ailleurs
        document.addEventListener('click', function(e) {
          if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
              menu.style.display = 'none';
            });
          }
        });
        
        // Toggle du menu hamburger
        const navbarToggler = document.querySelector('.navbar-toggler');
        if (navbarToggler) {
          navbarToggler.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('data-bs-target'));
            if (target) {
              if (target.classList.contains('show')) {
                target.classList.remove('show');
                target.style.display = 'none';
              } else {
                target.classList.add('show');
                target.style.display = 'block';
              }
            }
          });
        }
      }
    });
  </script>