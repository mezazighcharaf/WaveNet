<?php
  // Initializing session if needed
  session_start();
  
  // Include defi controller for frontoffice
  require_once __DIR__ . '/../../controller/FrontofficeDefiController.php';
  
  // Initialize controller
  $controller = new FrontofficeDefiController();
  
  // Vérifier si un ID est fourni
  if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: defis.php');
    exit;
  }
  
  $defiId = (int)$_GET['id'];
  $defi = $controller->getDefiById($defiId);
  
  // Si le défi n'existe pas, rediriger vers la liste des défis
  if (!$defi) {
    header('Location: defis.php');
    exit;
  }
  
  // Créer un utilisateur de démonstration si non connecté
  if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 'demo_user';
    $_SESSION['username'] = 'Utilisateur Démo';
    $_SESSION['email'] = 'demo@example.com';
    $_SESSION['points'] = 150;
    $_SESSION['role'] = 'user';
  }
  
  // Vérifier si l'utilisateur a déjà participé à ce défi
  $hasParticipated = $controller->hasUserParticipated($_SESSION['user_id'], $defiId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $defi['Titre_D']; ?> - Urbaverse</title>
  <link rel="stylesheet" href="../../assets/css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Styles améliorés pour la page de détail d'un défi */
    .defi-hero {
      background-color: var(--dark-green);
      background-image: linear-gradient(135deg, var(--dark-green) 0%, #1a3a2a 100%);
      color: white;
      padding: 70px 0 30px;
      position: relative;
      overflow: hidden;
      margin-top: 70px;
      box-shadow: 0 4px 30px rgba(0,0,0,0.15);
    }
    
    .defi-hero::before {
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
    
    .defi-hero::after {
      content: "";
      position: absolute;
      left: 10%;
      bottom: -150px;
      width: 300px;
      height: 300px;
      background-image: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
      border-radius: 50%;
    }
    
    .defi-header {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: flex-start;
      gap: 20px;
      margin-bottom: 20px;
    }
    
    .defi-title-section {
      flex: 1;
      min-width: 300px;
    }
    
    .defi-title {
      font-size: 38px;
      font-weight: 700;
      margin-bottom: 12px;
      position: relative;
      line-height: 1.2;
      letter-spacing: -0.5px;
    }
    
    .defi-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 15px;
    }
    
    .defi-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      border-radius: 30px;
      font-weight: 600;
      font-size: 13px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    .defi-badge.difficulty {
      background-color: rgba(255, 255, 255, 0.2);
    }
    
    .defi-badge.difficulty.facile {
      background-color: rgba(76, 175, 80, 0.3);
      color: #e8f5e9;
    }
    
    .defi-badge.difficulty.intermédiaire {
      background-color: rgba(255, 167, 38, 0.3);
      color: #fff8e1;
    }
    
    .defi-badge.difficulty.difficile {
      background-color: rgba(244, 67, 54, 0.3);
      color: #ffebee;
    }
    
    .defi-badge.points {
      background-color: rgba(33, 150, 243, 0.3);
      color: #e3f2fd;
    }
    
    .defi-badge.points::before {
      content: "🍃";
      font-size: 16px;
    }
    
    .defi-badge.dates {
      background-color: rgba(255, 255, 255, 0.2);
    }
    
    .defi-actions {
      display: flex;
      flex-direction: column;
      gap: 15px;
      min-width: 300px;
      max-width: 350px;
      background-color: rgba(255, 255, 255, 0.1);
      padding: 20px;
      border-radius: 16px;
      backdrop-filter: blur(5px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }
    
    .btn-participate {
      display: inline-block;
      padding: 15px 30px;
      background-color: var(--accent-green);
      color: white;
      border: none;
      border-radius: 12px;
      font-weight: 700;
      font-size: 16px;
      text-decoration: none;
      text-align: center;
      transition: all 0.3s ease;
      cursor: pointer;
      box-shadow: 0 6px 16px rgba(0,0,0,0.15);
      width: 100%;
    }
    
    .btn-participate:hover {
      background-color: #387c3b;
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    
    .btn-participate:disabled {
      background-color: #a5d6a7;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
    
    .btn-back {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      background-color: rgba(255, 255, 255, 0.2);
      color: white;
      border-radius: 10px;
      font-weight: 600;
      font-size: 15px;
      text-decoration: none;
      transition: all 0.2s ease;
      backdrop-filter: blur(5px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      margin-bottom: 25px;
      display: inline-block;
    }
    
    .btn-back::before {
      content: "←";
      font-size: 18px;
    }
    
    .btn-back:hover {
      background-color: rgba(255, 255, 255, 0.3);
      transform: translateX(-3px);
    }
    
    .notification {
      padding: 20px 24px;
      border-radius: 12px;
      margin: 20px 0;
      font-weight: 500;
      position: relative;
      display: flex;
      align-items: flex-start;
      gap: 16px;
    }
    
    .notification i {
      font-size: 24px;
      margin-top: 2px;
    }
    
    .notification.success {
      background-color: #e8f5e9;
      color: #2e7d32;
      border-left: 4px solid #4caf50;
    }
    
    .notification.error {
      background-color: #ffebee;
      color: #c62828;
      border-left: 4px solid #ef5350;
    }
    
    .defi-container {
      background-color: white;
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.08);
      padding: 40px;
      margin-top: -60px;
      position: relative;
      z-index: 10;
      border: 1px solid rgba(0,0,0,0.03);
    }
    
    .defi-content {
      background-color: #f8fbf8;
      background-image: linear-gradient(180deg, #ffffff 0%, #f8fbf8 100%);
      padding: 40px 0 80px;
    }
    
    .defi-section {
      margin-bottom: 40px;
      padding-bottom: 20px;
    }
    
    .defi-section:last-child {
      margin-bottom: 0;
      padding-bottom: 0;
      border-bottom: none;
    }
    
    .defi-section h2 {
      font-size: 26px;
      color: var(--text-color);
      margin-bottom: 24px;
      padding-bottom: 14px;
      border-bottom: 2px solid var(--light-green);
      position: relative;
    }
    
    .defi-section h2::after {
      content: "";
      position: absolute;
      left: 0;
      bottom: -2px;
      width: 60px;
      height: 2px;
      background-color: var(--accent-green);
    }
    
    .defi-section p {
      color: #444;
      line-height: 1.8;
      font-size: 16px;
    }
    
    .defi-details {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 30px;
      margin-top: 30px;
    }
    
    .detail-card {
      background-color: #f9fbf9;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 6px 16px rgba(0,0,0,0.04);
      transition: all 0.3s ease;
      border: 1px solid rgba(0,0,0,0.03);
    }
    
    .detail-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    
    .detail-card h3 {
      font-size: 18px;
      color: var(--text-color);
      margin-top: 0;
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .detail-card h3::before {
      content: "";
      width: 10px;
      height: 10px;
      background-color: var(--accent-green);
      border-radius: 50%;
      display: inline-block;
    }
    
    .detail-card p {
      margin: 0;
      color: #555;
      font-weight: 500;
    }
    
    .participation-form {
      margin-top: 40px;
      text-align: center;
    }
    
    .login-prompt {
      background-color: var(--light-green);
      border-radius: 16px;
      padding: 30px;
      text-align: center;
      margin-top: 30px;
      box-shadow: 0 6px 16px rgba(0,0,0,0.04);
    }
    
    .login-prompt p {
      margin-bottom: 20px;
      color: #444;
    }
    
    .participation-cta {
      background-color: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      padding: 20px;
      border-radius: 12px;
      margin-top: 0;
    }
    
    .participation-cta h3 {
      color: white;
      margin-top: 0;
      margin-bottom: 8px;
      font-size: 20px;
    }
    
    .participation-cta p {
      color: rgba(255, 255, 255, 0.9);
      margin-bottom: 15px;
      line-height: 1.5;
      font-size: 15px;
    }
    
    /* Style container */
    .container {
      max-width: var(--max-width);
      margin: 0 auto;
      padding: 0 20px;
    }
    
    /* Style du header */
    .main-header {
      background-color: var(--dark-green);
      padding: 15px;
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 999;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .main-header .container {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    
    .logo {
      display: flex;
      align-items: center;
    }
    
    .logo a {
      display: flex;
      align-items: center;
      text-decoration: none;
      gap: 15px;
    }
    
    .logo img {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
    }
    
    .main-nav {
      flex: 1;
      display: flex;
      justify-content: center;
    }
    
    .main-nav ul {
      display: flex;
      list-style: none;
      gap: 2.5rem;
      margin: 0;
      padding: 0;
    }
    
    .main-nav a {
      text-decoration: none;
      color: white;
      font-weight: 500;
      font-size: 16px;
      transition: all 0.2s ease;
      opacity: 0.9;
      padding: 8px 0;
      position: relative;
    }
    
    .main-nav a::after {
      content: "";
      position: absolute;
      left: 0;
      bottom: 0;
      width: 0;
      height: 2px;
      background-color: var(--accent-green);
      transition: width 0.3s ease;
    }
    
    .main-nav a:hover,
    .main-nav a.active {
      color: var(--accent-green);
      opacity: 1;
    }
    
    .main-nav a:hover::after,
    .main-nav a.active::after {
      width: 100%;
    }
    
    .user-info {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      color: white;
    }
    
    .user-info span {
      display: flex;
      align-items: center;
      gap: 8px;
      background-color: rgba(255, 255, 255, 0.1);
      padding: 8px 16px;
      border-radius: 30px;
      font-weight: 500;
      backdrop-filter: blur(5px);
    }
    
    .user-info i {
      color: var(--accent-green);
    }
    
    /* Style du footer */
    .main-footer {
      background-color: var(--dark-green);
      color: white;
      padding: 60px 0 30px;
      margin-top: 0;
    }
    
    .footer-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 40px;
      margin-bottom: 40px;
    }
    
    .footer-logo img {
      max-width: 160px;
      margin-bottom: 20px;
    }
    
    .footer-logo p {
      line-height: 1.7;
      opacity: 0.9;
    }
    
    .footer-links h3,
    .footer-contact h3 {
      color: var(--accent-green);
      margin-bottom: 20px;
      font-size: 20px;
      font-weight: 600;
    }
    
    .footer-links ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    
    .footer-links a,
    .social-icons a {
      color: white;
      text-decoration: none;
      transition: all 0.2s ease;
      display: block;
      margin-bottom: 12px;
      opacity: 0.8;
    }
    
    .footer-links a:hover,
    .social-icons a:hover {
      color: var(--accent-green);
      opacity: 1;
      transform: translateX(5px);
    }
    
    .footer-contact p {
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 12px;
      opacity: 0.9;
    }
    
    .social-icons {
      display: flex;
      gap: 20px;
      margin-top: 20px;
    }
    
    .social-icons a {
      background-color: rgba(255, 255, 255, 0.1);
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
      margin: 0;
    }
    
    .social-icons a:hover {
      background-color: var(--accent-green);
      transform: translateY(-5px);
      color: white;
    }
    
    .footer-bottom {
      padding-top: 20px;
      border-top: 1px solid rgba(255,255,255,0.1);
      text-align: center;
      opacity: 0.8;
    }
    
    @media (max-width: 768px) {
      .defi-hero {
        padding: 80px 0 40px;
        margin-top: 60px;
      }
      
      .defi-title {
        font-size: 32px;
      }
      
      .defi-container {
        padding: 30px 20px;
        margin-top: -60px;
      }
      
      .defi-header {
        flex-direction: column;
      }
      
      .defi-actions {
        max-width: 100%;
        width: 100%;
      }
      
      .defi-details {
        grid-template-columns: 1fr;
      }
      
      .main-header .container {
        flex-direction: column;
        gap: 15px;
      }
      
      .main-nav {
        width: 100%;
      }
      
      .main-nav ul {
        width: 100%;
        justify-content: space-between;
        gap: 0;
      }
      
      .user-info {
        width: 100%;
        justify-content: space-between;
      }
    }
    
    /* Styles améliorés pour les badges de statut dans la page de détail */
    .defi-badge.status {
      background-color: rgba(255, 255, 255, 0.2);
    }
    
    /* Actif - Vert */
    .defi-badge.status.actif {
      background-color: rgba(76, 175, 80, 0.3);
      color: #e8f5e9;
    }
    
    /* À venir - Bleu */
    .defi-badge.status.à-venir {
      background-color: rgba(33, 150, 243, 0.3);
      color: #e3f2fd;
    }
    
    /* Terminé - Rouge pâle */
    .defi-badge.status.terminé {
      background-color: rgba(239, 83, 80, 0.3);
      color: #ffebee;
    }
  </style>
</head>
<body>
  <!-- HEADER -->
  <header class="main-header">
    <div class="container">
      <div class="logo">
        <a href="index.php">
          <img src="../../assets/img/logo.jpg" alt="Logo Urbaverse">
          <span style="color: white; font-weight: 700; font-size: 24px;">Urbaverse</span>
        </a>
      </div>
      <nav class="main-nav">
        <ul>
          <li><a href="index.php">Accueil</a></li>
          <li><a href="defis.php" class="active">Défis</a></li>
          <li><a href="../backoffice/defis/index.php">Backoffice</a></li>
        </ul>
      </nav>
      <div class="user-info">
        <?php if(isset($_SESSION['points'])): ?>
          <span><i class="fas fa-leaf"></i> <?php echo $_SESSION['points']; ?> points</span>
        <?php else: ?>
          <span><i class="fas fa-leaf"></i> 0 points</span>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['username'])): ?>
          <span><i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?></span>
        <?php else: ?>
          <span><i class="fas fa-user"></i> Invité</span>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- DEFI HERO SECTION -->
  <section class="defi-hero">
    <div class="container">
      <a href="defis.php" class="btn-back">Retour aux défis</a>
      <div class="defi-header">
        <div class="defi-title-section">
          <h1 class="defi-title"><?php echo htmlspecialchars($defi['Titre_D']); ?></h1>
          <div class="defi-meta">
            <span class="defi-badge difficulty <?php echo strtolower($defi['Difficulte']); ?>"><?php echo $defi['Difficulte']; ?></span>
            <span class="defi-badge points"><?php echo $defi['Points_verts']; ?> points</span>
            <span class="defi-badge dates"><?php echo date('d/m/Y', strtotime($defi['Date_Debut'])); ?> - <?php echo date('d/m/Y', strtotime($defi['Date_Fin'])); ?></span>
            <span class="defi-badge status <?php echo strtolower(str_replace(' ', '-', $defi['Statut_D'])); ?>"><?php echo $defi['Statut_D']; ?></span>
          </div>
        </div>
        
        <div class="defi-actions">
          <?php if ($hasParticipated): ?>
            <div class="notification success">
              <i class="fas fa-check-circle"></i>
              <div class="status-message">
                <h3>Vous avez déjà participé à ce défi</h3>
                <p>Merci pour votre contribution à l'environnement! Vous avez gagné <?php echo $defi['Points_verts']; ?> points verts.</p>
              </div>
            </div>
          <?php else: ?>
            <div class="participation-cta">
              <h3>Prêt à relever le défi?</h3>
              <p>Participez à ce défi écologique et gagnez <?php echo $defi['Points_verts']; ?> points verts tout en contribuant à l'amélioration de votre quartier!</p>
              <a href="participate.php?id=<?php echo $defi['Id_Defi']; ?>" class="btn-participate">Participer à ce défi</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- DEFI CONTENT SECTION -->
  <section class="defi-content">
    <div class="container">
      <div class="defi-container">
        <div class="defi-section">
          <h2>Description</h2>
          <p><?php echo nl2br(htmlspecialchars($defi['Description_D'])); ?></p>
        </div>
        
        <div class="defi-section">
          <h2>Objectif du défi</h2>
          <p><?php echo nl2br(htmlspecialchars($defi['Objectif'])); ?></p>
        </div>
        
        <div class="defi-section">
          <h2>Détails supplémentaires</h2>
          <div class="defi-details">
            <div class="detail-card">
              <h3>Points à gagner</h3>
              <p><?php echo $defi['Points_verts']; ?> points verts</p>
            </div>
            
            <div class="detail-card">
              <h3>Difficulté</h3>
              <p><?php echo $defi['Difficulte']; ?></p>
            </div>
            
            <div class="detail-card">
              <h3>Quartier</h3>
              <p><?php echo $defi['Id_Quartier'] ? "Quartier #" . $defi['Id_Quartier'] : "Tous les quartiers"; ?></p>
            </div>
            
            <div class="detail-card">
              <h3>Période</h3>
              <p>Du <?php echo date('d/m/Y', strtotime($defi['Date_Debut'])); ?> au <?php echo date('d/m/Y', strtotime($defi['Date_Fin'])); ?></p>
            </div>
          </div>
        </div>

        <!-- Nouvelle section pour les étapes -->
        <div class="defi-section">
          <h2>Étapes</h2>
          <?php
            // Inclure le contrôleur des étapes
            require_once __DIR__ . '/../../controller/EtapeController.php';
            
            // Récupérer les étapes de ce défi
            $etapeController = new EtapeController();
            $etapes = $etapeController->getEtapesByDefi($defi['Id_Defi']);
            
            if (empty($etapes)) {
              echo '<p>Aucune étape n\'est disponible pour ce défi.</p>';
            } else {
          ?>
            <div class="defi-details">
              <?php foreach($etapes as $etape): ?>
                <div class="detail-card">
                  <h3><?php echo htmlspecialchars($etape['Titre_E']); ?></h3>
                  <p><?php echo htmlspecialchars($etape['Description_E']); ?></p>
                  <div style="margin-top: 15px; font-size: 0.9em; color: #666;">
                    <span style="display: block; margin-bottom: 5px;">
                      <strong>Ordre:</strong> <?php echo htmlspecialchars($etape['Ordre']); ?>
                    </span>
                    <span style="display: block;">
                      <strong>Points bonus:</strong> <?php echo htmlspecialchars($etape['Points_Bonus']); ?> points
                    </span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="main-footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-logo">
          <img src="../../assets/img/logo.jpg" alt="Logo Urbaverse" style="width: 80px; height: 80px; border-radius: 50%;">
          <p>Ensemble, rendons notre quartier plus vert et plus durable.</p>
        </div>
        <div class="footer-links">
          <h3>Liens rapides</h3>
          <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="defis.php">Défis</a></li>
            <li><a href="../backoffice/defis/index.php">Backoffice</a></li>
          </ul>
        </div>
        <div class="footer-contact">
          <h3>Contact</h3>
          <p><i class="fas fa-envelope"></i> contact@urbaverse.fr</p>
          <p><i class="fas fa-phone"></i> +33 1 23 45 67 89</p>
          <div class="social-icons">
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin"></i></a>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Urbaverse. Tous droits réservés.</p>
      </div>
    </div>
  </footer>

  <script src="../../assets/js/script.js"></script>
</body>
</html> 