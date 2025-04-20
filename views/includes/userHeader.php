<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$userName = isset($_SESSION['user_id']) ? (isset($userDbData) && method_exists($userDbData, 'getPrenom') ? $userDbData->getPrenom() : 'Utilisateur') : '';
?>
<header class="main-header user-header">
  <div class="nav-container">
    <div class="logo">
      <a href="/WaveNet/views/frontoffice/userDashboard.php" style="text-decoration:none; color:inherit;"><h1>WaveNet</h1></a>
    </div>
    <nav>
      <ul class="nav-links">
        <li><a href="/WaveNet/views/frontoffice/defis.php" class="<?php echo (isset($activePage) && $activePage === 'defis') ? 'active' : ''; ?>">Défis</a></li>
        <li><a href="/WaveNet/views/frontoffice/activites.php" class="<?php echo (isset($activePage) && $activePage === 'activites') ? 'active' : ''; ?>">Activités</a></li>
        <li><a href="/WaveNet/views/frontoffice/manageTransport.php" class="<?php echo (isset($activePage) && $activePage === 'transport') ? 'active' : ''; ?>">Transports</a></li>
      </ul>
    </nav>
    <div class="user-actions">
      <span class="user-name" style="color:var(--white); font-weight:600; margin-right:1rem;">
        <?php echo htmlspecialchars($userName); ?>
      </span>
      <a href="/WaveNet/views/frontoffice/userDashboard.php" class="btn btn-secondary" style="font-size:0.95rem; padding:0.5rem 1rem;">Profil</a>
      <a href="/WaveNet/controller/UserController.php?action=logout" class="btn btn-primary" style="font-size:0.95rem; padding:0.5rem 1rem;">Déconnexion</a>
    </div>
  </div>
</header>
