<?php
  // Initializing session if needed
  session_start();
  
  // Include defi controller for frontoffice
  require_once __DIR__ . '/../../controller/FrontofficeDefiController.php';
  
  // Initialize controller
  $controller = new FrontofficeDefiController();
  
  // Récupérer le type de tri
  $sort = isset($_GET['sort']) ? $_GET['sort'] : 'difficulty';
  
  // Récupérer l'ordre de tri
  $order = isset($_GET['order']) ? $_GET['order'] : 'asc';
  
  // Récupérer le filtre de difficulté s'il existe
  $difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : 'all';
  
  // Récupérer le filtre de statut s'il existe
  $status = isset($_GET['status']) ? $_GET['status'] : 'all';
  
  // Liste des difficultés valides (avec première lettre en majuscule)
  $validDifficulties = ['Facile', 'Intermédiaire', 'Difficile'];
  
  // Liste des statuts valides
  $validStatuses = ['Actif', 'À venir', 'Terminé'];
  
  // Récupérer les défis selon le tri et le filtre
  if ($sort === 'title') {
      // Trier par ordre alphabétique du titre
      $defis = $controller->getAllDefisSorted($order);
  } else if ($sort === 'status' && $status !== 'all' && in_array($status, $validStatuses)) {
      // Filtrer par statut
      $defis = $controller->getDefisByStatus($status);
  } else if ($sort === 'difficulty' && $difficulty !== 'all' && in_array(ucfirst(strtolower($difficulty)), $validDifficulties)) {
      // Standardiser le format de la difficulté
      $standardizedDifficulty = ucfirst(strtolower($difficulty));
      $defis = $controller->getDefisByDifficulty($standardizedDifficulty);
      $difficulty = $standardizedDifficulty; // Pour l'affichage des filtres actifs
  } else {
      // Par défaut, récupérer tous les défis
      $defis = $controller->getAllDefis();
  }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Urbaverse - Défis Écologiques</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/Projet_Web/assets/css/frontoffice.css" />
</head>
<body>
  <!-- HEADER -->
  <header class="main-header">
    <nav class="nav-container">
      <div class="logo">
        <a href="index.php">
          <img src="/Projet_Web/assets/img/logo.jpg" alt="Logo Urbaverse" class="logo-img">
          <span class="logo-title">Urbaverse</span>
        </a>
      </div>
      <ul class="nav-links">
        <li><a href="index.php">Accueil</a></li>
        <li><a href="defis.php" class="active">Défis</a></li>
        <li><a href="../backoffice/dashboard/index.php">Backoffice</a></li>
      </ul>
      <div class="user-actions">
        <?php if(isset($_SESSION['points'])): ?>
          <span class="points"><?php echo $_SESSION['points']; ?> points</span>
        <?php else: ?>
          <span class="points">0 points</span>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['username'])): ?>
          <a href="profile.php" class="btn btn-secondary"><?php echo $_SESSION['username']; ?></a>
        <?php else: ?>
          <a href="login.php" class="btn btn-secondary">Connexion</a>
        <?php endif; ?>
      </div>
    </nav>
  </header>

  <!-- HERO SECTION -->
  <section class="page-hero">
    <div class="container">
      <h1>Défis écologiques</h1>
      <p>Participez aux défis pour gagner des points verts et contribuer à un avenir urbain plus durable. Chaque action compte !</p>
    </div>
  </section>
  
  <!-- FILTERS SECTION -->
  <section class="filters-section">
    <div class="container">
      <div class="filters-container">
        <h3 class="filter-title">Trier et filtrer</h3>
        <div class="sorting-controls">
          <form method="get" action="defis.php" class="filter-form">
            <select name="sort" id="sort-filter" class="sort-select" onchange="this.form.submit()">
              <option value="title" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'title') ? 'selected' : ''; ?>>Ordre alphabétique</option>
              <option value="status" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'status') ? 'selected' : ''; ?>>Par statut</option>
              <option value="difficulty" <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'difficulty') ? 'selected' : ''; ?>>Par difficulté</option>
            </select>
          </form>
          
          <?php if(isset($_GET['sort']) && $_GET['sort'] == 'title'): ?>
            <div class="sorting-buttons">
              <a href="defis.php?sort=title&order=asc" class="sort-btn <?php echo (!isset($_GET['order']) || $_GET['order'] == 'asc') ? 'active' : ''; ?>">
                <i class="fas fa-sort-alpha-down"></i>
                <span>A-Z</span>
              </a>
              <a href="defis.php?sort=title&order=desc" class="sort-btn <?php echo (isset($_GET['order']) && $_GET['order'] == 'desc') ? 'active' : ''; ?>">
                <i class="fas fa-sort-alpha-down-alt"></i>
                <span>Z-A</span>
              </a>
            </div>
          <?php endif; ?>
        </div>
        
        <?php if (!isset($_GET['sort']) || $_GET['sort'] == 'difficulty'): ?>
          <div class="difficulty-filters">
            <a href="defis.php?sort=difficulty&difficulty=all" class="filter-pill <?php echo $difficulty === 'all' ? 'active' : ''; ?>">Tous les défis</a>
            <a href="defis.php?sort=difficulty&difficulty=Facile" class="filter-pill facile <?php echo $difficulty === 'Facile' ? 'active' : ''; ?>">Facile</a>
            <a href="defis.php?sort=difficulty&difficulty=Intermédiaire" class="filter-pill intermediaire <?php echo $difficulty === 'Intermédiaire' ? 'active' : ''; ?>">Intermédiaire</a>
            <a href="defis.php?sort=difficulty&difficulty=Difficile" class="filter-pill difficile <?php echo $difficulty === 'Difficile' ? 'active' : ''; ?>">Difficile</a>
          </div>
        <?php elseif ($_GET['sort'] == 'status'): ?>
          <div class="status-filters">
            <a href="defis.php?sort=status&status=all" class="filter-pill <?php echo (!isset($_GET['status']) || $_GET['status'] === 'all') ? 'active' : ''; ?>">Tous les statuts</a>
            <a href="defis.php?sort=status&status=Actif" class="filter-pill status-active <?php echo (isset($_GET['status']) && $_GET['status'] === 'Actif') ? 'active' : ''; ?>">Actif</a>
            <a href="defis.php?sort=status&status=À venir" class="filter-pill status-upcoming <?php echo (isset($_GET['status']) && $_GET['status'] === 'À venir') ? 'active' : ''; ?>">À venir</a>
            <a href="defis.php?sort=status&status=Terminé" class="filter-pill status-completed <?php echo (isset($_GET['status']) && $_GET['status'] === 'Terminé') ? 'active' : ''; ?>">Terminé</a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- DEFIS SECTION -->
  <section class="defis-container">
    <div class="container">
      <?php if($defis->rowCount() > 0): ?>
        <div class="defis-grid">
          <?php while($defi = $defis->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="defi-card">
              <div class="defi-card-header">
                <span class="difficulty <?php echo strtolower($defi['Difficulte']); ?>">
                  <?php echo htmlspecialchars($defi['Difficulte']); ?>
                </span>
                <span class="badge-points">
                  <i class="fa-solid fa-seedling"></i> <?php echo $defi['Points_verts']; ?> points
                </span>
              </div>
              <div class="defi-card-body">
                <h3><?php echo $defi['Titre_D']; ?></h3>
                <p><?php echo htmlspecialchars($defi['Description_D']); ?></p>
                <span class="status-badge-home <?php echo strtolower(str_replace(' ', '-', trim($defi['Statut_D']))); ?>">
                  <?php echo htmlspecialchars($defi['Statut_D']); ?>
                </span>
              </div>
              <div class="defi-card-footer">
                <span class="date-badge"><?php echo date('d/m/Y', strtotime($defi['Date_Debut'])); ?> - <?php echo date('d/m/Y', strtotime($defi['Date_Fin'])); ?></span>
                <a href="defi.php?id=<?php echo $defi['Id_Defi']; ?>" class="btn-view-defi">Voir le défi</a>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <h3>Aucun défi disponible</h3>
          <p>Il n'y a pas de défis correspondant à ces critères pour le moment. Essayez de modifier vos filtres ou revenez plus tard.</p>
          <?php if($difficulty !== 'all'): ?>
            <a href="defis.php?difficulty=all" class="btn btn-primary">Voir tous les défis</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <h4>Urbaverse</h4>
        <p>Ensemble pour un avenir urbain durable</p>
      </div>
      <div class="footer-section">
        <h4>Liens Rapides</h4>
        <ul>
          <li><a href="index.php">Accueil</a></li>
          <li><a href="defis.php">Défis</a></li>
          <li><a href="../backoffice/dashboard/index.php">Backoffice</a></li>
        </ul>
      </div>
      <div class="footer-section">
        <h4>Suivez-nous</h4>
        <div class="social-links">
          <a href="https://twitter.com" target="_blank">Twitter</a>
          <a href="https://facebook.com" target="_blank">Facebook</a>
          <a href="https://instagram.com" target="_blank">Instagram</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?php echo date('Y'); ?> Urbaverse. Tous droits réservés.</p>
    </div>
  </footer>
</body>
</html> 