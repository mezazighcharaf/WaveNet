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
  <style>
    /* Variables globales */
    :root {
      --primary-color: #4caf50;
      --primary-dark: #388e3c;
      --primary-light: #81c784;
      --accent-color: #f1c40f;
      --accent-dark: #f39c12;
      --dark-color: #2e4f3e;
      --light-color: #ecf7ed;
      --text-color: #2c3e50;
      --text-light: #7f8c8d;
      --white: #fff;
      --gray-100: #f8f9fa;
      --gray-200: #e9ecef;
      --gray-300: #dee2e6;
      --gray-400: #ced4da;
      --gray-500: #adb5bd;
      --gray-600: #6c757d;
      --gray-700: #495057;
      --gray-800: #343a40;
      --gray-900: #212529;
      --max-width: 1200px;
      --border-radius: 12px;
      --border-radius-sm: 8px;
      --transition: all 0.3s ease;
      --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      --box-shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.1);
      --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }

    /* Reset et styles de base */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: var(--font-family);
      line-height: 1.6;
      color: var(--text-color);
      background-color: var(--light-color);
    }
    
    .container {
      max-width: var(--max-width);
      margin: 0 auto;
      padding: 0 20px;
    }
    
    /* Header */
    .main-header {
      background-color: var(--dark-color);
      box-shadow: var(--box-shadow);
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
      height: 80px;
      padding: 0 30px;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .logo a {
      display: flex;
      align-items: center;
      text-decoration: none;
    }
    
    .logo img {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      border: 2px solid var(--primary-color);
      transition: var(--transition);
    }
    
    .logo:hover img {
      transform: scale(1.05);
    }
    
    .logo span {
      color: var(--white);
      font-weight: 700;
      font-size: 24px;
    }
    
    .nav-links {
      display: flex;
      list-style: none;
      gap: 2rem;
    }
    
    .nav-links a {
      color: var(--white);
      text-decoration: none;
      font-weight: 500;
      padding: 0.5rem 1rem;
      border-radius: var(--border-radius-sm);
      transition: var(--transition);
      opacity: 0.9;
    }
    
    .nav-links a:hover,
    .nav-links a.active {
      background-color: var(--primary-color);
      opacity: 1;
      transform: translateY(-2px);
    }
    
    .user-actions {
      display: flex;
      align-items: center;
      gap: 1.5rem;
    }
    
    .points {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      background-color: rgba(255, 255, 255, 0.1);
      padding: 0.5rem 1rem;
      border-radius: 50px;
      color: var(--white);
      font-weight: 600;
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0.75rem 1.5rem;
      border-radius: var(--border-radius-sm);
      font-weight: 600;
      text-decoration: none;
      transition: var(--transition);
    }
    
    .btn-secondary {
      background-color: var(--white);
      color: var(--primary-color);
      border: 2px solid var(--primary-color);
    }
    
    .btn-secondary:hover {
      background-color: var(--primary-color);
      color: var(--white);
      transform: translateY(-2px);
    }
    
    /* Hero Section */
    .hero {
      background: linear-gradient(135deg, var(--dark-color) 0%, #1a3a2a 100%);
      color: var(--white);
      padding: 160px 0 80px;
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
      gap: 4rem;
      position: relative;
      z-index: 2;
    }
    
    .hero-text {
      flex: 1;
      max-width: 600px;
    }
    
    .hero-text h2 {
      font-size: 3rem;
      font-weight: 800;
      line-height: 1.2;
      margin-bottom: 1.5rem;
      color: var(--white);
    }
    
    .hero-text p {
      font-size: 1.25rem;
      margin-bottom: 2rem;
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
    }
    
    .section-header {
      text-align: center;
      margin-bottom: 3rem;
    }
    
    .section-header h2 {
      font-size: 2.5rem;
      color: var(--dark-color);
      margin-bottom: 1rem;
      position: relative;
      display: inline-block;
    }
    
    .section-header h2::after {
      content: "";
      display: block;
      width: 70%;
      height: 4px;
      background-color: var(--primary-color);
      margin: 1rem auto 0;
      border-radius: 2px;
    }
    
    .section-header p {
      color: var(--text-light);
      font-size: 1.25rem;
      max-width: 600px;
      margin: 0 auto;
    }
    
    .defis-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
      margin-top: 2rem;
    }
    
    .defi-card {
      background-color: var(--white);
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      overflow: hidden;
      transition: var(--transition);
    }
    
    .defi-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--box-shadow-hover);
    }
    
    .defi-card-header {
      padding: 1.5rem;
      background-color: var(--light-color);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .difficulty {
      padding: 0.5rem 1rem;
      border-radius: 50px;
      font-size: 0.875rem;
      font-weight: 600;
      text-transform: uppercase;
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
    
    .defi-card-body {
      padding: 1.5rem;
    }
    
    .defi-card-body h3 {
      font-size: 1.25rem;
      margin-bottom: 1rem;
      color: var(--text-color);
    }
    
    .defi-card-body p {
      color: var(--text-light);
      margin-bottom: 1.5rem;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .defi-card-footer {
      padding: 1.5rem;
      background-color: var(--gray-100);
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-top: 1px solid var(--gray-200);
    }
    
    .dates {
      font-size: 0.875rem;
      color: var(--text-light);
    }
    
    .btn-small {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      background-color: var(--primary-color);
      color: var(--white);
      border-radius: var(--border-radius-sm);
      font-size: 0.875rem;
      font-weight: 600;
      text-decoration: none;
      transition: var(--transition);
    }
    
    .btn-small:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
    }
    
    /* Footer */
    footer {
      background-color: var(--dark-color);
      color: var(--white);
      padding: 60px 0 20px;
    }
    
    .footer-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 3rem;
      margin-bottom: 3rem;
    }
    
    .footer-section h4 {
      font-size: 1.25rem;
      margin-bottom: 1.5rem;
      color: var(--primary-color);
      position: relative;
    }
    
    .footer-section h4::after {
      content: "";
      display: block;
      width: 50px;
      height: 3px;
      background-color: var(--primary-color);
      margin-top: 0.5rem;
    }
    
    .footer-section ul {
      list-style: none;
    }
    
    .footer-section ul li {
      margin-bottom: 0.75rem;
    }
    
    .footer-section a {
      color: var(--white);
      text-decoration: none;
      transition: var(--transition);
      opacity: 0.9;
    }
    
    .footer-section a:hover {
      color: var(--primary-color);
      opacity: 1;
      transform: translateX(5px);
    }
    
    .social-links {
      display: flex;
      gap: 1rem;
    }
    
    .footer-bottom {
      text-align: center;
      padding-top: 2rem;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    /* Responsive */
    @media (max-width: 992px) {
      .hero-content {
        flex-direction: column;
        text-align: center;
      }
      
      .hero-text {
        max-width: 100%;
      }
      
      .hero-img {
        max-width: 100%;
      }
      
      .nav-links {
        gap: 1rem;
      }
    }
    
    @media (max-width: 768px) {
      .nav-container {
        flex-direction: column;
        height: auto;
        padding: 1rem;
      }
      
      .nav-links {
        margin: 1rem 0;
      }
      
      .user-actions {
        margin-top: 1rem;
      }
      
      .hero {
        padding: 120px 0 60px;
      }
      
      .hero-text h2 {
        font-size: 2rem;
      }
      
      .section-header h2 {
        font-size: 2rem;
      }
    }
  </style>
  <link rel="stylesheet" href="../../assets/css/style.css" />
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