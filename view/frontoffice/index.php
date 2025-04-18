<?php
  // Initializing session if needed
  session_start();
  
  // Include defi controller for frontoffice with correct path
  require_once __DIR__ . '/../../controller/FrontofficeDefiController.php';
  
  // Initialize controller and get popular defis
  $defiController = new FrontofficeDefiController();
  $popularDefis = $defiController->getPopularDefis(4); // Get top 4 popular defis
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Urbaverse - Accueil</title>
  <link rel="stylesheet" href="../../assets/css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Styles améliorés pour la page d'accueil */
    :root {
      --primary-color: var(--accent-green);
      --secondary-color: var(--dark-green);
      --accent-color: #81C784;
      --dark-color: var(--dark-green);
      --light-color: var(--light-green);
      --text-color: #333;
      --text-light: #666;
      --border-radius: 10px;
      --box-shadow: 0 6px 15px rgba(0,0,0,0.1);
      --transition: all 0.3s ease;
    }
    
    body {
      font-family: 'Inter', sans-serif;
      line-height: 1.6;
      color: var(--text-color);
      background-color: var(--light-color);
      margin: 0;
      padding: 0;
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    /* Header Styles */
    .main-header {
      background-color: var(--dark-green);
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
    }
    
    .nav-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      height: 70px;
      padding: 0 30px;
      max-width: 1200px;
      margin: 0 auto;
    }
    
    .logo a {
      display: flex;
      align-items: center;
      text-decoration: none;
    }
    
    .logo img {
      border-radius: 50%;
      margin-right: 10px;
      object-fit: cover;
      border: 2px solid var(--accent-green);
      transition: transform 0.3s ease;
    }
    
    .logo:hover img {
      transform: scale(1.05);
    }
    
    .logo h1 {
      font-size: 24px;
      font-weight: 700;
      color: var(--white);
      margin: 0;
    }
    
    .nav-links {
      display: flex;
      list-style: none;
      margin: 0;
      padding: 0;
    }
    
    .nav-links li {
      margin: 0 15px;
    }
    
    .nav-links a {
      text-decoration: none;
      color: var(--white);
      font-weight: 500;
      padding: 8px 0;
      border-bottom: 2px solid transparent;
      transition: var(--transition);
      opacity: 0.9;
    }
    
    .nav-links a:hover, .nav-links a.active {
      color: var(--accent-green);
      border-bottom-color: var(--accent-green);
      opacity: 1;
    }
    
    .user-actions {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .points {
      background-color: rgba(255, 255, 255, 0.2);
      color: var(--white);
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    
    .points::before {
      content: "🍃";
      font-size: 14px;
    }
    
    .btn {
      display: inline-block;
      padding: 10px 20px;
      border-radius: var(--border-radius);
      text-decoration: none;
      font-weight: 600;
      transition: var(--transition);
      text-align: center;
    }
    
    .btn-primary {
      background-color: var(--accent-green);
      color: var(--white);
      border: none;
    }
    
    .btn-primary:hover {
      background-color: #43a047; /* Légèrement plus sombre */
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .btn-secondary {
      background-color: var(--white);
      color: var(--accent-green);
      border: 2px solid var(--accent-green);
    }
    
    .btn-secondary:hover {
      background-color: var(--accent-green);
      color: var(--white);
    }
    
    /* Hero Section */
    .hero {
      background: linear-gradient(135deg, var(--dark-green) 0%, #1a3a2a 100%);
      color: var(--white);
      padding: 120px 0 80px;
      margin-top: 70px;
      position: relative;
      overflow: hidden;
    }
    
    .hero::before {
      content: "";
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      background-image: url('../../assets/img/pattern.png');
      background-size: cover;
      opacity: 0.1;
      z-index: 1;
    }
    
    .hero-content {
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      position: relative;
      z-index: 2;
    }
    
    .hero-text {
      max-width: 500px;
      margin-right: 40px;
    }
    
    .hero-text h2 {
      font-size: 42px;
      font-weight: 800;
      margin: 0 0 20px;
      line-height: 1.2;
      color: var(--white);
    }
    
    .hero-text p {
      font-size: 18px;
      margin-bottom: 30px;
      opacity: 0.9;
      color: var(--white);
    }
    
    .hero-img {
      flex: 1;
      max-width: 500px;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: var(--box-shadow);
    }
    
    .hero-img img {
      width: 100%;
      height: auto;
      display: block;
      transition: transform 1s ease;
    }
    
    .hero-img:hover img {
      transform: scale(1.05);
    }
    
    /* Popular Defis Section */
    .popular-defis {
      padding: 80px 0;
      background-color: var(--light-color);
    }
    
    .section-header {
      text-align: center;
      margin-bottom: 50px;
    }
    
    .section-header h2 {
      font-size: 32px;
      color: var(--dark-green);
      margin-bottom: 15px;
      position: relative;
      display: inline-block;
    }
    
    .section-header h2::after {
      content: "";
      display: block;
      width: 70%;
      height: 3px;
      background-color: var(--accent-green);
      margin: 10px auto 0;
      border-radius: 2px;
    }
    
    .section-header p {
      color: var(--text-light);
      font-size: 18px;
      max-width: 600px;
      margin: 0 auto;
    }
    
    .defis-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 30px;
      margin-top: 30px;
    }
    
    .defi-card {
      background-color: var(--white);
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      overflow: hidden;
      transition: var(--transition);
      display: flex;
      flex-direction: column;
      height: 100%;
    }
    
    .defi-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 20px rgba(0,0,0,0.15);
    }
    
    .defi-card-header {
      padding: 15px 20px;
      background-color: var(--light-color);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .difficulty {
      padding: 5px 10px;
      border-radius: 30px;
      font-size: 14px;
      font-weight: 600;
    }
    
    .difficulty.facile {
      background-color: #E8F5E9;
      color: #2E7D32;
    }
    
    .difficulty.intermédiaire {
      background-color: #FFF8E1;
      color: #F57F17;
    }
    
    .difficulty.difficile {
      background-color: #FFEBEE;
      color: #C62828;
    }
    
    .points {
      font-weight: 600;
      color: var(--primary-color);
      display: flex;
      align-items: center;
      gap: 5px;
    }
    
    .defi-card-body {
      padding: 20px;
      flex-grow: 1;
    }
    
    .defi-card-body h3 {
      font-size: 18px;
      margin-top: 0;
      margin-bottom: 15px;
      color: var(--text-color);
    }
    
    .defi-card-body p {
      color: var(--text-light);
      margin-bottom: 0;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .defi-card-footer {
      padding: 15px 20px;
      background-color: #f9f9f9;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-top: 1px solid #eee;
    }
    
    .dates {
      font-size: 14px;
      color: var(--text-light);
    }
    
    .btn-small {
      display: inline-block;
      padding: 8px 16px;
      background-color: var(--accent-green);
      color: var(--white);
      border-radius: var(--border-radius);
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
      transition: var(--transition);
    }
    
    .btn-small:hover {
      background-color: #43a047;
    }
    
    .section-footer {
      text-align: center;
      margin-top: 50px;
    }
    
    /* Footer Styles */
    footer {
      background-color: var(--dark-green);
      color: var(--white);
      padding: 60px 0 20px;
    }
    
    .footer-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 40px;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    .footer-section h4 {
      font-size: 18px;
      margin-top: 0;
      margin-bottom: 20px;
      position: relative;
      color: var(--accent-green);
    }
    
    .footer-section h4::after {
      content: "";
      display: block;
      width: 50px;
      height: 2px;
      background-color: var(--accent-green);
      margin-top: 10px;
    }
    
    .footer-section ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    
    .footer-section ul li {
      margin-bottom: 10px;
    }
    
    .footer-section a {
      color: #ddd;
      text-decoration: none;
      transition: var(--transition);
    }
    
    .footer-section a:hover {
      color: var(--accent-green);
    }
    
    .social-links {
      display: flex;
      gap: 15px;
    }
    
    .footer-bottom {
      text-align: center;
      padding-top: 30px;
      margin-top: 30px;
      border-top: 1px solid #444;
    }
    
    /* Responsive Design */
    @media (max-width: 992px) {
      .hero-content {
        flex-direction: column;
      }
      
      .hero-text {
        margin-right: 0;
        margin-bottom: 40px;
        text-align: center;
        max-width: 100%;
      }
      
      .hero-img {
        max-width: 100%;
      }
      
      .section-header h2 {
        font-size: 28px;
      }
    }
    
    @media (max-width: 768px) {
      .nav-container {
        flex-direction: column;
        height: auto;
        padding: 15px;
      }
      
      .nav-links {
        margin: 15px 0;
      }
      
      .user-actions {
        margin-top: 15px;
      }
      
      .hero {
        padding: 100px 0 60px;
      }
      
      .hero-text h2 {
        font-size: 32px;
      }
      
      .defis-grid {
        grid-template-columns: 1fr;
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
        <li><a href="index.php" class="active">Accueil</a></li>
        <li><a href="defis.php">Défis</a></li>
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
                <span class="difficulty <?php echo strtolower($defi['Difficulte']); ?>"><?php echo $defi['Difficulte']; ?></span>
                <span class="points"><?php echo $defi['Points_verts']; ?> points</span>
              </div>
              <div class="defi-card-body">
                <h3><?php echo $defi['Titre_D']; ?></h3>
                <p><?php echo substr($defi['Description_D'], 0, 100) . '...'; ?></p>
              </div>
              <div class="defi-card-footer">
                <span class="dates"><?php echo date('d/m/Y', strtotime($defi['Date_Debut'])); ?> - <?php echo date('d/m/Y', strtotime($defi['Date_Fin'])); ?></span>
                <a href="defi.php?id=<?php echo $defi['Id_Defi']; ?>" class="btn-small">Voir le défi</a>
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