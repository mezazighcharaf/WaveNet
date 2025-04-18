<?php
  // Initializing session if needed
  session_start();
  
  // Include defi controller for frontoffice
  require_once __DIR__ . '/../../controller/FrontofficeDefiController.php';
  
  // Initialize controller
  $controller = new FrontofficeDefiController();
  
  // Récupérer le filtre de difficulté s'il existe
  $difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : 'all';
  
  // Créer un utilisateur de démonstration si non connecté
  if (!isset($_SESSION['user_id'])) {
      $_SESSION['user_id'] = 'demo_user';
      $_SESSION['username'] = 'Utilisateur Démo';
      $_SESSION['email'] = 'demo@example.com';
      $_SESSION['points'] = 150;
      $_SESSION['role'] = 'user';
  }
  
  // Liste des difficultés valides (avec première lettre en majuscule)
  $validDifficulties = ['Facile', 'Intermédiaire', 'Difficile'];
  
  // Récupérer les défis selon le filtre
  if ($difficulty !== 'all' && in_array(ucfirst(strtolower($difficulty)), $validDifficulties)) {
      // Standardiser le format de la difficulté
      $standardizedDifficulty = ucfirst(strtolower($difficulty));
      $defis = $controller->getDefisByDifficulty($standardizedDifficulty);
      $difficulty = $standardizedDifficulty; // Pour l'affichage des filtres actifs
  } else {
      $defis = $controller->getAllDefis();
      $difficulty = 'all'; // Réinitialiser à 'all' si une valeur invalide a été fournie
  }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Urbaverse - Défis Écologiques</title>
  <link rel="stylesheet" href="../../assets/css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Styles améliorés pour la page des défis */
    .page-hero {
      background-color: var(--dark-green);
      background-image: linear-gradient(135deg, var(--dark-green) 0%, #1a3a2a 100%);
      color: white;
      padding: 100px 0 80px;
      position: relative;
      overflow: hidden;
      margin-top: 70px;
      box-shadow: 0 4px 30px rgba(0,0,0,0.15);
    }
    
    .page-hero::before {
      content: "";
      position: absolute;
      right: 0;
      top: 0;
      width: 400px;
      height: 400px;
      background-image: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
      border-radius: 50%;
      transform: translate(30%, -30%);
    }
    
    .page-hero::after {
      content: "";
      position: absolute;
      left: 10%;
      bottom: 0;
      width: 300px;
      height: 300px;
      background-image: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
      border-radius: 50%;
      transform: translateY(40%);
    }
    
    .page-hero h1 {
      font-size: 48px;
      font-weight: 700;
      margin-bottom: 20px;
      position: relative;
      letter-spacing: -0.5px;
    }
    
    .page-hero p {
      font-size: 19px;
      opacity: 0.95;
      max-width: 600px;
      line-height: 1.7;
      font-weight: 400;
    }
    
    .filters-section {
      background-color: white;
      padding: 30px 0;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      position: relative;
      z-index: 5;
      border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .filters-container {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 20px;
    }
    
    .filter-title {
      font-size: 18px;
      font-weight: 600;
      color: var(--text-color);
      margin: 0;
    }
    
    .filter-options {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
    }
    
    .filter-pill {
      display: inline-block;
      padding: 12px 24px;
      border-radius: 30px;
      font-weight: 500;
      font-size: 15px;
      transition: all 0.3s ease;
      background-color: #f0f4f1;
      color: var(--text-color);
      text-decoration: none;
      border: 2px solid transparent;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .filter-pill:hover {
      background-color: #e4ebe6;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .filter-pill.active {
      background-color: var(--accent-green);
      color: white;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(56, 124, 59, 0.2);
    }
    
    .filter-pill.facile {
      border-color: #c8e6c9;
    }
    
    .filter-pill.intermediaire {
      border-color: #ffecb3;
    }
    
    .filter-pill.difficile {
      border-color: #ffccbc;
    }
    
    .filter-pill.facile.active {
      background-color: #66bb6a;
    }
    
    .filter-pill.intermediaire.active {
      background-color: #ffa726;
    }
    
    .filter-pill.difficile.active {
      background-color: #ef5350;
    }
    
    .defis-container {
      padding: 80px 0;
      background-color: var(--light-green);
      background-image: linear-gradient(180deg, #f8fbf8 0%, var(--light-green) 100%);
    }
    
    .defis-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
      gap: 30px;
      margin-top: 30px;
    }
    
    .defi-card {
      background-color: white;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      height: 100%;
      position: relative;
      border: 1px solid rgba(0,0,0,0.03);
    }
    
    .defi-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 35px rgba(0,0,0,0.12);
    }
    
    .defi-card-header {
      padding: 22px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid rgba(0,0,0,0.05);
      background-color: #fcfcfc;
    }
    
    .difficulty {
      display: inline-block;
      padding: 8px 16px;
      border-radius: 30px;
      font-size: 14px;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    
    .difficulty.facile {
      background-color: #e8f5e9;
      color: #2e7d32;
    }
    
    .difficulty.intermédiaire {
      background-color: #fff8e1;
      color: #f57f17;
    }
    
    .difficulty.difficile {
      background-color: #ffebee;
      color: #c62828;
    }
    
    .points-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      border-radius: 30px;
      background-color: #e3f2fd;
      color: #1565c0;
      font-weight: 600;
      font-size: 14px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    
    .points-badge::before {
      content: "🍃";
      font-size: 16px;
    }
    
    .defi-card-body {
      padding: 28px;
      flex-grow: 1;
      background: linear-gradient(to bottom, #ffffff 0%, #fafafa 100%);
    }
    
    .defi-card-body h3 {
      margin-top: 0;
      margin-bottom: 18px;
      font-size: 22px;
      color: var(--text-color);
      line-height: 1.4;
      font-weight: 600;
    }
    
    .defi-card-body p {
      color: #555;
      line-height: 1.7;
      margin-bottom: 0;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
      font-size: 15px;
    }
    
    .defi-card-footer {
      padding: 20px 28px;
      background-color: #f9fbf9;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-top: 1px solid rgba(0,0,0,0.05);
    }
    
    .date-range {
      font-size: 14px;
      color: #666;
      font-weight: 500;
    }
    
    .btn-view-defi {
      display: inline-block;
      padding: 10px 22px;
      background-color: var(--accent-green);
      color: white;
      border-radius: 8px;
      font-weight: 600;
      font-size: 14px;
      text-decoration: none;
      transition: all 0.2s ease;
      box-shadow: 0 4px 12px rgba(56, 124, 59, 0.2);
    }
    
    .btn-view-defi:hover {
      background-color: #387c3b;
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(56, 124, 59, 0.25);
    }
    
    .empty-state {
      text-align: center;
      padding: 80px 30px;
      background-color: white;
      border-radius: 16px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.08);
      margin: 40px auto;
      max-width: 700px;
    }
    
    .empty-state h3 {
      margin-bottom: 20px;
      color: #444;
      font-size: 24px;
    }
    
    .empty-state p {
      color: #666;
      max-width: 500px;
      margin: 0 auto 28px;
      line-height: 1.7;
      font-size: 16px;
    }
    
    .empty-state .btn-primary {
      margin: 0 auto;
      display: inline-block;
      padding: 12px 28px;
      font-size: 16px;
      border-radius: 8px;
      font-weight: 600;
    }
    
    @media (max-width: 768px) {
      .page-hero {
        padding: 70px 0 60px;
        margin-top: 60px;
      }
      
      .page-hero h1 {
        font-size: 36px;
      }
      
      .defis-grid {
        grid-template-columns: 1fr;
        padding: 0 15px;
        gap: 25px;
      }
      
      .filters-container {
        flex-direction: column;
        align-items: flex-start;
        padding: 0 15px;
      }
      
      .filter-title {
        margin-bottom: 12px;
      }
      
      .filter-options {
        width: 100%;
        justify-content: space-between;
      }
      
      .filter-pill {
        padding: 10px 18px;
        font-size: 14px;
      }
      
      .defis-container {
        padding: 60px 0;
      }
    }
  </style>
</head>
<body>
  <!-- HEADER -->
  <header class="main-header">
    <nav class="nav-container">
      <div class="logo">
        <a href="index.php">
          <img src="../../assets/img/logo.jpg" alt="Logo Urbaverse" width="70" height="70" style="border-radius: 50%; margin-right: 10px; vertical-align: middle;">
          <span style="color: var(--white); font-weight: 700; font-size: 24px; vertical-align: middle;">Urbaverse</span>
        </a>
      </div>
      <ul class="nav-links">
        <li><a href="index.php">Accueil</a></li>
        <li><a href="defis.php" class="active">Défis</a></li>
        <li><a href="../backoffice/defis/index.php">Backoffice</a></li>
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
        <h3 class="filter-title">Filtrer par difficulté</h3>
        <div class="filter-options">
          <a href="defis.php?difficulty=all" class="filter-pill <?php echo $difficulty === 'all' ? 'active' : ''; ?>">Tous les défis</a>
          <a href="defis.php?difficulty=Facile" class="filter-pill facile <?php echo $difficulty === 'Facile' ? 'active' : ''; ?>">Facile</a>
          <a href="defis.php?difficulty=Intermédiaire" class="filter-pill intermediaire <?php echo $difficulty === 'Intermédiaire' ? 'active' : ''; ?>">Intermédiaire</a>
          <a href="defis.php?difficulty=Difficile" class="filter-pill difficile <?php echo $difficulty === 'Difficile' ? 'active' : ''; ?>">Difficile</a>
        </div>
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
                <span class="difficulty <?php echo strtolower($defi['Difficulte']); ?>"><?php echo $defi['Difficulte']; ?></span>
                <span class="points-badge"><?php echo $defi['Points_verts']; ?> points</span>
              </div>
              <div class="defi-card-body">
                <h3><?php echo htmlspecialchars($defi['Titre_D']); ?></h3>
                <p><?php echo htmlspecialchars(substr($defi['Description_D'], 0, 150)) . (strlen($defi['Description_D']) > 150 ? '...' : ''); ?></p>
              </div>
              <div class="defi-card-footer">
                <span class="date-range"><?php echo date('d/m/Y', strtotime($defi['Date_Debut'])); ?> - <?php echo date('d/m/Y', strtotime($defi['Date_Fin'])); ?></span>
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
          <li><a href="../backoffice/defis/index.php">Backoffice</a></li>
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