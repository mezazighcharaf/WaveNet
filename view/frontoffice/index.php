<?php
  // Initializing session if needed
  session_start();
  
  // Include controllers
  require_once __DIR__ . '/../../controller/FrontofficeDefiController.php';
  require_once __DIR__ . '/../../controller/DefiController.php';
  require_once __DIR__ . '/../../controller/EtapeController.php';
  
  // Initialize controllers
  $frontDefiController = new FrontofficeDefiController();
  $defiController = new DefiController();
  $etapeController = new EtapeController();
  
  // Update statuses automatically
  $defiController->updateDefiStatuses();
  $etapeController->updateEtapeStatuses();
  
  // Get popular defis
  $popularDefis = $frontDefiController->getPopularDefis(4); // Get top 4 popular defis
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Urbaverse - Accueil</title>
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
        <li><a href="index.php" class="active">Accueil</a></li>
        <li><a href="defis.php">Défis</a></li>
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
  <section class="hero">
    <div class="hero-content">
      <div class="hero-text">
        <h2>Découvrez le monde urbain vert</h2>
        <p>
          Rejoignez notre communauté pour bâtir ensemble des espaces verts
          et favoriser la transition écologique de nos villes.
        </p>
        <?php if(!isset($_SESSION['username'])): ?>
          <a href="register.php" class="btn btn-primary">Commencer l'aventure</a>
        <?php else: ?>
          <a href="defis.php" class="btn btn-primary">Voir les défis</a>
        <?php endif; ?>
      </div>
      <div class="hero-img">
        <img src="https://www.saintgermainbouclesdeseine.fr/wp-content/uploads/2021/07/sartrouville-quartier-indes-1536x806.jpg" alt="Paysage urbain" />
      </div>
    </div>
  </section>

  <!-- DEFIS POPULAIRES SECTION -->
  <section class="popular-defis">
    <div class="container">
      <div class="section-header">
        <h2>Défis populaires</h2>
        <p>Découvrez les défis écologiques les plus populaires de notre communauté</p>
      </div>
      
      <div class="defis-grid">
        <?php if($popularDefis->rowCount() > 0): ?>
          <?php while($defi = $popularDefis->fetch(PDO::FETCH_ASSOC)): ?>
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
        <?php else: ?>
          <div class="no-defis">
            <p>Aucun défi disponible pour le moment.</p>
          </div>
        <?php endif; ?>
      </div>
      
      <div class="section-footer">
        <a href="defis.php" class="btn btn-primary">Voir tous les défis</a>
      </div>
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