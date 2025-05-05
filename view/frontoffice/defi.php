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

    .stickman-animation-container {
        margin: 0 auto;
        width: 100%;
        max-width: 920px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .stickman-frame {
        position: relative;
        width: 900px;
        height: 300px;
        border: 1px solid #ccc;
        margin-bottom: 20px;
        overflow: hidden;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .stickman-controls {
        display: flex;
        gap: 20px;
        margin-top: 10px;
        margin-bottom: 30px;
    }
    
    /* Confettis */
    #confetti-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        pointer-events: none;
        z-index: 9999;
    }
    
    .confetti {
        position: fixed;
        width: 10px;
        height: 10px;
        background-color: #f00;
        opacity: 0.8;
        z-index: 9999;
        animation: fall linear forwards;
    }
    
    @keyframes fall {
        0% { 
            transform: translateY(-50px) rotate(0deg); 
            opacity: 1;
        }
        100% { 
            transform: translateY(100vh) rotate(360deg); 
            opacity: 0;
        }
    }

    /* Message de succès */
    #message-succes {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: rgba(255, 255, 255, 0.9);
        padding: 20px 40px;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        font-size: 24px;
        font-weight: bold;
        color: #4CAF50;
        text-align: center;
        opacity: 0;
        transition: opacity 0.5s;
        pointer-events: none;
        z-index: 10000;
    }

    /* Styles pour les infobulles */
    .tooltip {
      position: absolute;
      background-color: white;
      border: 1px solid #ccc;
      border-radius: 6px;
      padding: 10px 15px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      font-size: 14px;
      max-width: 250px;
      z-index: 9000;
      pointer-events: none;
      opacity: 0;
      transition: opacity 0.2s ease;
    }
    
    .tooltip.visible {
      opacity: 1;
    }
    
    .tooltip h4 {
      margin: 0 0 5px 0;
      color: var(--accent-green);
      font-size: 16px;
      font-weight: 600;
    }
    
    .tooltip p {
      margin: 5px 0 0 0;
      color: #333;
      line-height: 1.4;
    }
    
    .tooltip .tooltip-meta {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
      margin-top: 8px;
      color: #666;
    }
    
    .point-hover {
      cursor: pointer;
      transition: r 0.2s ease;
    }
    
    .point-hover:hover {
      r: 6;
      fill: var(--accent-green);
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
          <li><a href="../backoffice/dashboard/index.php">Backoffice</a></li>
        </ul>
      </nav>
      <div class="user-info">
        <?php if(isset($_SESSION['points']) && $_SESSION['user_id'] !== 'demo_user'): ?>
          <span><i class="fas fa-leaf"></i> <?php echo $_SESSION['points']; ?> points</span>
        <?php else: ?>
          <span><i class="fas fa-leaf"></i> 0 points</span>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['username']) && $_SESSION['user_id'] !== 'demo_user'): ?>
          <span><i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?></span>
          <a href="logout.php" style="color: white; margin-left: 10px;" title="Déconnexion"><i class="fas fa-sign-out-alt"></i></a>
        <?php else: ?>
          <a href="login.php" style="text-decoration: none;"><span><i class="fas fa-user"></i> Connexion</span></a>
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
            // Récupérer les étapes de ce défi
            require_once __DIR__ . '/../../controller/EtapeController.php';
            $etapeController = new EtapeController();
            $etapes = $etapeController->getEtapesByDefi($defi['Id_Defi']);
            $nombreEtapes = count($etapes);
          ?>
          <!-- Stickman Animation Container -->
          <div class="stickman-animation-container">
            <!-- Stickman Animation Frame -->
            <div class="stickman-frame">
                <svg width="900" height="300" viewBox="0 0 900 300">
                    <!-- Points fixes de départ et d'arrivée -->
                    <?php
                      // Calculer les segments et points en fonction du nombre d'étapes
                      $segments = array();
                      
                      // Segments fixes
                      if ($nombreEtapes == 1) {
                        // S'il n'y a qu'une étape, un seul segment entre début et fin
                        echo '<line id="path-segment-0" x1="120" y1="200" x2="740" y2="200" stroke="black" stroke-width="3"/>';
                        echo '<circle cx="120" cy="200" r="4" fill="black" class="point-hover" data-point="0"/>';
                        echo '<circle cx="740" cy="200" r="4" fill="black" class="point-hover" data-point="1"/>';
                      } 
                      else if ($nombreEtapes == 2) {
                        // S'il y a deux étapes, on partage en deux segments uniformes
                        echo '<line id="path-segment-0" x1="120" y1="200" x2="430" y2="200" stroke="black" stroke-width="3"/>';
                        echo '<line id="path-segment-1" x1="430" y1="200" x2="740" y2="200" stroke="black" stroke-width="3"/>';
                        echo '<circle cx="120" cy="200" r="4" fill="black" class="point-hover" data-point="0"/>';
                        echo '<circle cx="430" cy="200" r="4" fill="black" class="point-hover" data-point="1"/>';
                        echo '<circle cx="740" cy="200" r="4" fill="black" class="point-hover" data-point="2"/>';
                      }
                      else {
                        // Par défaut, on utilise 3 segments (comportement original)
                        echo '<line id="path-segment-0" x1="120" y1="200" x2="360" y2="200" stroke="black" stroke-width="3"/>';
                        echo '<line id="path-segment-1" x1="360" y1="200" x2="600" y2="200" stroke="black" stroke-width="3"/>';
                        echo '<line id="path-segment-2" x1="600" y1="200" x2="740" y2="200" stroke="black" stroke-width="3"/>';
                        echo '<circle cx="120" cy="200" r="4" fill="black" class="point-hover" data-point="0"/>';
                        echo '<circle cx="360" cy="200" r="4" fill="black" class="point-hover" data-point="1"/>';
                        echo '<circle cx="600" cy="200" r="4" fill="black" class="point-hover" data-point="2"/>';
                        echo '<circle cx="740" cy="200" r="4" fill="black" class="point-hover" data-point="3"/>';
                      }
                    ?>
                    
                    <!-- Drapeau exactement au bout de la ligne, même taille que le stickman -->
                    <g transform="translate(740, 100)">
                        <!-- Poteau du drapeau plus grand -->
                        <line x1="0" y1="0" x2="0" y2="100" stroke="black" stroke-width="2"/>
                        
                        <!-- Triangle du drapeau plus grand -->
                        <polygon points="0,0 30,15 0,30" fill="black"/>
                    </g>
                    
                    <!-- Stickman - position fixe à gauche avec ID pour l'animation -->
                    <g id="stickman" transform="translate(60, 200)">
                        <!-- Tête -->
                        <circle cx="0" cy="-100" r="20" stroke="black" stroke-width="2" fill="white"/>
                        
                        <!-- Casquette rose -->
                        <path d="M-23,-115 C-20,-125 20,-125 23,-115 L23,-110 L-23,-110 Z" fill="#FF69B4" stroke="black" stroke-width="1.5"/>
                        
                        <!-- Visière de la casquette (vers l'avant) -->
                        <path d="M-20,-112 L-35,-112 L-25,-105 L-20,-105 Z" fill="#FF69B4" stroke="black" stroke-width="1.5"/>
                        
                        <!-- Yeux -->
                        <circle cx="-7" cy="-105" r="2" fill="black"/>
                        <circle cx="7" cy="-105" r="2" fill="black"/>
                        
                        <!-- Sourire -->
                        <path d="M-10,-90 Q0,-85 10,-90" stroke="black" stroke-width="2" fill="none"/>
                        
                        <!-- Corps - exactement 60px de long -->
                        <line x1="0" y1="-80" x2="0" y2="-20" stroke="black" stroke-width="2"/>
                        
                        <!-- Bras -->
                        <line id="arm-left" x1="-20" y1="-60" x2="0" y2="-60" stroke="black" stroke-width="2"/>
                        <line id="arm-right" x1="0" y1="-60" x2="20" y2="-60" stroke="black" stroke-width="2"/>
                        
                        <!-- Jambes - partant du même point, formant un V exact -->
                        <line id="leg-left" x1="0" y1="-20" x2="-20" y2="0" stroke="black" stroke-width="2"/>
                        <line id="leg-right" x1="0" y1="-20" x2="20" y2="0" stroke="black" stroke-width="2"/>
                    </g>
                </svg>
                  </div>
            
            <div class="stickman-controls">
                <button id="btnAvancer" class="btn btn-primary">Avancer</button>
                <button id="btnRetour" class="btn btn-secondary" disabled>Retour</button>
                </div>
            
            <!-- Conteneur pour l'infobulle -->
            <div id="tooltip" class="tooltip" style="display: none; position: absolute; z-index: 9999;"></div>
            
            <!-- Conteneur pour les confettis sur tout l'écran -->
            <div id="confetti-container"></div>
            
            <!-- Message de succès -->
            <div id="message-succes">Défi réussi avec succès !</div>
            </div>
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
            <li><a href="../backoffice/dashboard/index.php">Backoffice</a></li>
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
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const stickman = document.getElementById('stickman');
        const legLeft = document.getElementById('leg-left');
        const legRight = document.getElementById('leg-right');
        const armLeft = document.getElementById('arm-left');
        const armRight = document.getElementById('arm-right');
        const btnAvancer = document.getElementById('btnAvancer');
        const btnRetour = document.getElementById('btnRetour');
        const tooltip = document.getElementById('tooltip');
        
        // Vérification si le temps est écoulé (date de fin du défi dépassée)
        const dateFinDefi = new Date("<?php echo $defi['Date_Fin']; ?>");
        const dateActuelle = new Date();
        const tempsEcoule = dateActuelle > dateFinDefi;
        
        // Si le temps est écoulé, configurer l'état "temps écoulé"
        if (tempsEcoule) {
            console.log("Temps écoulé pour ce défi!");
            
            // Désactiver les boutons
            btnAvancer.disabled = true;
            btnRetour.disabled = true;
            
            // Afficher le message "Temps écoulé"
            const messageTempsEcoule = document.createElement('div');
            messageTempsEcoule.id = 'message-temps-ecoule';
            messageTempsEcoule.style.position = 'fixed';
            messageTempsEcoule.style.top = '50%';
            messageTempsEcoule.style.left = '50%';
            messageTempsEcoule.style.transform = 'translate(-50%, -50%)';
            messageTempsEcoule.style.backgroundColor = 'rgba(255, 0, 0, 0.8)';
            messageTempsEcoule.style.color = 'white';
            messageTempsEcoule.style.padding = '20px 40px';
            messageTempsEcoule.style.borderRadius = '10px';
            messageTempsEcoule.style.fontSize = '24px';
            messageTempsEcoule.style.fontWeight = 'bold';
            messageTempsEcoule.style.zIndex = '10000';
            messageTempsEcoule.style.boxShadow = '0 0 20px rgba(0, 0, 0, 0.2)';
            messageTempsEcoule.innerHTML = 'Temps écoulé !';
            document.body.appendChild(messageTempsEcoule);
            
            // Faire pleurer le stickman
            faireStickmanPleurer();
            
            // Faire tomber des larmes
            faireTomberLarmes();
        }
        
        // Données des étapes (obtenues du PHP)
        const etapesData = [
            <?php 
            // Première étape = début du chemin
            echo "{ title: 'Départ', description: 'Point de départ de votre défi', ordre: 'Début', points: '0' },";
            
            // Étapes intermédiaires depuis la base de données
            foreach($etapes as $index => $etape) {
                echo "{";
                echo "title: '" . addslashes($etape['Titre_E']) . "',";
                echo "description: '" . addslashes($etape['Description_E']) . "',";
                echo "ordre: '" . addslashes($etape['Ordre']) . "',";
                echo "points: '" . addslashes($etape['Points_Bonus']) . "'";
                echo "},";
            }
            
            // Dernière étape = arrivée (drapeau)
            echo "{ title: 'Arrivée', description: 'Félicitations, vous avez terminé ce défi !', ordre: 'Fin', points: '" . $defi['Points_verts'] . "' }";
            ?>
        ];
        
        // Gestion des infobulles sur les points du chemin
        const pointsHover = document.querySelectorAll('.point-hover');
        console.log('Points trouvés:', pointsHover.length); // Débogage
        
        // Créer un nouvel élément tooltip qui sera plus fiable
        const tooltipElement = document.createElement('div');
        tooltipElement.className = 'tooltip';
        tooltipElement.style.display = 'none';
        tooltipElement.style.position = 'absolute';
        tooltipElement.style.zIndex = '9999';
        tooltipElement.style.backgroundColor = 'white';
        tooltipElement.style.border = '1px solid #ccc';
        tooltipElement.style.borderRadius = '8px';
        tooltipElement.style.padding = '15px';
        tooltipElement.style.boxShadow = '0 5px 15px rgba(0,0,0,0.2)';
        tooltipElement.style.maxWidth = '250px';
        document.body.appendChild(tooltipElement);
        
        pointsHover.forEach(point => {
            point.addEventListener('mouseenter', function(e) {
                console.log('Survol détecté sur point:', this.getAttribute('data-point')); // Débogage
                const pointIndex = parseInt(this.getAttribute('data-point'));
                if (pointIndex < etapesData.length) {
                    const etape = etapesData[pointIndex];
                    console.log('Étape trouvée:', etape); // Débogage
                    
                    // Créer le contenu de l'infobulle
                    let content = `<h4 style="margin: 0 0 8px 0; color: #4CAF50; font-size: 16px; font-weight: 600;">${etape.title}</h4>`;
                    content += `<p style="margin: 5px 0; color: #333; line-height: 1.4;">${etape.description}</p>`;
                    content += `<div style="display: flex; justify-content: space-between; font-size: 12px; margin-top: 10px; color: #666;">`;
                    content += `<span>Ordre: ${etape.ordre}</span>`;
                    content += `<span>Points: ${etape.points}</span>`;
                    content += `</div>`;
                    
                    tooltipElement.innerHTML = content;
                    
                    // Positionner l'infobulle près du point
                    const pointRect = this.getBoundingClientRect();
                    console.log('Position du point:', pointRect.left, pointRect.top); // Débogage
                    
                    // Positionnement direct par rapport à la fenêtre
                    const tooltipX = pointRect.left + window.scrollX;
                    const tooltipY = pointRect.top + window.scrollY - 140; // Bien au-dessus du point
                    
                    tooltipElement.style.left = `${tooltipX}px`;
                    tooltipElement.style.top = `${tooltipY}px`;
                    tooltipElement.style.display = 'block';
                    tooltipElement.style.opacity = '1';
                    
                    console.log('Tooltip positionné à:', tooltipElement.style.left, tooltipElement.style.top); // Débogage
                }
            });
            
            point.addEventListener('mouseleave', function() {
                tooltipElement.style.display = 'none';
            });
        });
        
        // Chemins à colorer - version simplifiée
        const pathSegments = [];
        let i = 0;
        let segment = document.getElementById('path-segment-' + i);
        while (segment) {
            pathSegments.push(segment);
            i++;
            segment = document.getElementById('path-segment-' + i);
        }
        
        // Positions des étapes - hardcodées pour comportement fiable
        <?php if ($nombreEtapes == 1): ?>
        // Une seule étape : début, drapeau, position finale
        const etapes = [
            60,    // Position initiale
            120,   // Premier point
            840    // Position finale après le drapeau (SUPPRIMÉ le point au drapeau)
        ];
        <?php elseif ($nombreEtapes == 2): ?>
        // Deux étapes : début, point intermédiaire, position finale
        const etapes = [
            60,    // Position initiale
            120,   // Premier point
            430,   // Point intermédiaire
            840    // Position finale après le drapeau (SUPPRIMÉ le point au drapeau)
        ];
        <?php else: ?>
        // Configuration par défaut
        const etapes = [
            60,    // Position initiale
            120,   // Premier point
            360,   // Deuxième point
            600,   // Troisième point
            840    // Position finale après le drapeau (SUPPRIMÉ le point au drapeau)
        ];
        <?php endif; ?>
        
        let etapeActuelle = 0;
        let enMouvement = false;
        let successCelebrated = false;
        let intervalMarche = null;
        let intervalCelebration = null;
        
        // Fonction pour colorer le segment de chemin parcouru
        function colorerChemin(index) {
            if (index < 0 || index >= pathSegments.length) return;
            pathSegments[index].setAttribute('stroke', '#4CAF50'); // Vert
            pathSegments[index].setAttribute('stroke-width', '4'); // Un peu plus épais pour l'effet
        }
        
        // Fonction pour réinitialiser les couleurs de tous les segments de chemin
        function reinitialiserChemins() {
            pathSegments.forEach(segment => {
                segment.setAttribute('stroke', 'black');
                segment.setAttribute('stroke-width', '3');
            });
        }
        
        // Fonction pour animer le stickman qui sautille avec les bras en l'air
        function animerCelebration() {
            // Arrêter toute animation en cours
            if (intervalCelebration) {
                clearInterval(intervalCelebration);
            }
            
            // Lever les bras en l'air (position fixe)
            armLeft.setAttribute('x1', '0');
            armLeft.setAttribute('y1', '-60');
            armLeft.setAttribute('x2', '-15');
            armLeft.setAttribute('y2', '-85');
            
            armRight.setAttribute('x1', '0');
            armRight.setAttribute('y1', '-60');
            armRight.setAttribute('x2', '15');
            armRight.setAttribute('y2', '-85');
            
            // Animation de sautillement
            let hauteur = 0;
            let montant = true;
            let cycles = 0;
            
            intervalCelebration = setInterval(() => {
                if (montant) {
                    hauteur += 2;
                    if (hauteur >= 15) {
                        montant = false;
                    }
                } else {
                    hauteur -= 2;
                    if (hauteur <= 0) {
                        montant = true;
                        cycles++;
                    }
                }
                
                // Déplacer tout le stickman vers le haut/bas
                stickman.setAttribute('transform', `translate(840, ${200 - hauteur})`);
                
                // Bouger les jambes pour l'effet de saut
                if (hauteur > 7) {
                    // Jambes plus écartées en montant
                    legLeft.setAttribute('x2', '-25');
                    legRight.setAttribute('x2', '25');
                } else {
                    // Jambes moins écartées en descendant
                    legLeft.setAttribute('x2', '-15');
                    legRight.setAttribute('x2', '15');
                }
                
                // Arrêter l'animation après un certain nombre de cycles
                if (cycles > 100) { // Animation en continu, mais on peut limiter si besoin
                    clearInterval(intervalCelebration);
                    
                    // Remettre les jambes en position normale
                    legLeft.setAttribute('x2', '-20');
                    legRight.setAttribute('x2', '20');
                }
            }, 50); // Animation plus rapide pour un sautillement naturel
        }
        
        // Fonction pour déplacer le stickman avec animation de marche
        function deplacerStickman(vers) {
            if (enMouvement) return;
            // Vérifier si le temps est écoulé avant tout déplacement
            if (tempsEcoule) {
                console.log("Déplacement impossible - temps écoulé !");
                return;
            }
            
            enMouvement = true;
            
            const depart = etapes[etapeActuelle];
            const arrivee = etapes[vers];
            const distance = arrivee - depart;
            const duree = Math.abs(distance) / 100; // Vitesse constante
            const depart_time = Date.now();
            
            // Réinitialiser les bras à leur position normale pendant le déplacement
            armLeft.setAttribute('x1', '-20');
            armLeft.setAttribute('y1', '-60');
            armLeft.setAttribute('x2', '0');
            armLeft.setAttribute('y2', '-60');
            
            armRight.setAttribute('x1', '0');
            armRight.setAttribute('y1', '-60');
            armRight.setAttribute('x2', '20');
            armRight.setAttribute('y2', '-60');
            
            // Mouvement de marche animé pendant le déplacement
            let pasGauche = true;
            let cycleMarche = 0;
            
            intervalMarche = setInterval(() => {
                if (pasGauche) {
                    // Pas avec jambe gauche - style original amélioré
                    legLeft.setAttribute('x2', '-10');  // Jambe gauche légèrement en avant
                    legLeft.setAttribute('y2', '-10');  // Légèrement pliée
                    legRight.setAttribute('x2', '20');  // Jambe droite en arrière tendue
                    legRight.setAttribute('y2', '0');   // Au sol
                } else {
                    // Pas avec jambe droite - style original amélioré
                    legLeft.setAttribute('x2', '-20');  // Jambe gauche en arrière tendue
                    legLeft.setAttribute('y2', '0');    // Au sol
                    legRight.setAttribute('x2', '10');  // Jambe droite légèrement en avant
                    legRight.setAttribute('y2', '-10'); // Légèrement pliée
                }
                
                // Alterner les pas
                pasGauche = !pasGauche;
                cycleMarche++;
                
            }, 150); // Vitesse de l'animation de marche
            
            // Animation de déplacement fluide
            function animer() {
                const now = Date.now();
                const elapsed = (now - depart_time) / 1000; // temps écoulé en secondes
                const ratio = Math.min(elapsed / duree, 1); // proportion de l'animation terminée
                
                const currentPos = depart + ratio * distance;
                stickman.setAttribute('transform', `translate(${currentPos}, 200)`);
                
                if (ratio < 1) {
                    requestAnimationFrame(animer);
                } else {
                    // Animation terminée
                    etapeActuelle = vers;
                    enMouvement = false;
                    
                    // Arrêter l'animation de marche
                    clearInterval(intervalMarche);
                    
                    // Remettre les jambes en position normale
                    legLeft.setAttribute('x2', '-20');
                    legLeft.setAttribute('y2', '0');
                    legRight.setAttribute('x2', '20');
                    legRight.setAttribute('y2', '0');
                    
                    // Colorer le segment de chemin si on avance
                    if (distance > 0) {
                        // Approche simplifiée : colorier en fonction de l'étape atteinte
                        reinitialiserChemins(); // D'abord effacer toutes les colorations
                        
                        // Colorier tous les segments jusqu'à l'étape actuelle
                        const nbSegmentsAColorier = etapeActuelle > 1 ? etapeActuelle - 1 : 0;
                        
                        for (let i = 0; i < nbSegmentsAColorier; i++) {
                            if (i < pathSegments.length) {
                                pathSegments[i].setAttribute('stroke', '#4CAF50'); // Vert
                                pathSegments[i].setAttribute('stroke-width', '4'); // Un peu plus épais
                            }
                        }
                        
                        // Si on arrive à la position finale, colorier aussi le dernier segment
                        if (etapeActuelle === etapes.length - 1 && pathSegments.length > 0) {
                            // Colorier tous les segments jusqu'à la fin
                            for (let i = 0; i < pathSegments.length; i++) {
                                pathSegments[i].setAttribute('stroke', '#4CAF50');
                                pathSegments[i].setAttribute('stroke-width', '4');
                            }
                        }
                    } else if (distance < 0) {
                        // Si on recule, réinitialiser puis colorier jusqu'à l'étape actuelle
                        reinitialiserChemins();
                        
                        const nbSegmentsAColorier = etapeActuelle > 1 ? etapeActuelle - 1 : 0;
                        
                        for (let i = 0; i < nbSegmentsAColorier; i++) {
                            if (i < pathSegments.length) {
                                pathSegments[i].setAttribute('stroke', '#4CAF50');
                                pathSegments[i].setAttribute('stroke-width', '4');
                            }
                        }
                    }
                    
                    // Mettre à jour les boutons
                    btnRetour.disabled = (etapeActuelle === 0);
                    btnAvancer.disabled = (etapeActuelle === etapes.length - 1);
                    
                    // Si c'est l'étape finale, déclencher la célébration
                    if (etapeActuelle === etapes.length - 1 && !successCelebrated) {
                        successCelebrated = true;
                        animerCelebration();
                        celebrerSucces();
                        
                        // Notifier le parent que le défi est réussi
                        if (window.parent) {
                            window.parent.postMessage('success', '*');
                        }
                    }
                }
            }
            
            animer();
        }
        
        // Gestionnaires d'événements pour les boutons
        btnAvancer.addEventListener('click', function() {
            if (!enMouvement && etapeActuelle < etapes.length - 1) {
                deplacerStickman(etapeActuelle + 1);
            }
        });
        
        btnRetour.addEventListener('click', function() {
            if (!enMouvement && etapeActuelle > 0) {
                deplacerStickman(etapeActuelle - 1);
                
                // Réinitialiser l'état de célébration si on revient en arrière
                if (successCelebrated) {
                    successCelebrated = false;
                    if (intervalCelebration) {
                        clearInterval(intervalCelebration);
                    }
                    
                    // Remettre les bras et les jambes en position normale
                    armLeft.setAttribute('x1', '-20');
                    armLeft.setAttribute('y1', '-60');
                    armLeft.setAttribute('x2', '0');
                    armLeft.setAttribute('y2', '-60');
                    
                    armRight.setAttribute('x1', '0');
                    armRight.setAttribute('y1', '-60');
                    armRight.setAttribute('x2', '20');
                    armRight.setAttribute('y2', '-60');
                    
                    legLeft.setAttribute('x2', '-20');
                    legLeft.setAttribute('y2', '0');
                    legRight.setAttribute('x2', '20');
                    legRight.setAttribute('y2', '0');
                }
            }
        });
        
        // Fonction pour créer et afficher les confettis
        function celebrerSucces() {
            // Ne pas célébrer si le temps est écoulé
            if (tempsEcoule) {
                console.log("Pas de célébration - temps écoulé !");
                return;
            }
            
            // Afficher le message de succès
            document.getElementById('message-succes').style.opacity = "1";
            
            // Vider le conteneur de confettis existants
            const confettiContainer = document.getElementById('confetti-container');
            confettiContainer.innerHTML = '';
            
            // Créer les confettis pour couvrir tout l'écran
            const confettiCount = 700; // Augmenté pour plus de densité
            const colors = ['#f00', '#0f0', '#00f', '#ff0', '#0ff', '#f0f', '#fd0', '#0fd', '#f83', '#8f3', '#3f8', '#83f', '#f38'];
            
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                
                // Position aléatoire sur toute la largeur
                const startPosX = Math.random() * window.innerWidth;
                
                // Propriétés CSS aléatoires
                confetti.style.left = startPosX + 'px';
                confetti.style.top = '-50px'; // Commence au-dessus de l'écran
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                
                // Tailles variables pour plus de diversité
                const size = Math.random() * 15 + 5;
                confetti.style.width = size + 'px';
                confetti.style.height = size + 'px';
                
                // Formes variées (carrés, cercles, triangles)
                const shapeNum = Math.floor(Math.random() * 3);
                if (shapeNum === 0) {
                    // Cercle
                    confetti.style.borderRadius = '50%';
                } else if (shapeNum === 1) {
                    // Carré
                    confetti.style.borderRadius = '0';
                } else {
                    // Forme étoile/losange
                    confetti.style.borderRadius = '0';
                    confetti.style.transform = 'rotate(45deg)';
                }
                
                // Vitesse de chute aléatoire
                const fallDuration = Math.random() * 5 + 3; // 3-8 secondes
                confetti.style.animation = `fall ${fallDuration}s linear forwards`;
                
                // Délai avant l'apparition pour créer un effet continu
                const delay = Math.random() * 5;
                confetti.style.animationDelay = `${delay}s`;
                
                confettiContainer.appendChild(confetti);
                
                // Supprimer le confetti après son animation
                setTimeout(() => {
                    confetti.remove();
                }, (fallDuration + delay) * 1000 + 500); // +500ms pour être sûr
            }
            
            // Faire disparaître le message après un certain temps
            setTimeout(() => {
                document.getElementById('message-succes').style.opacity = "0";
            }, 5000);
            
            // Vider complètement le conteneur après la durée maximale
            setTimeout(() => {
                confettiContainer.innerHTML = '';
            }, 15000); // 15 secondes pour être sûr que tous les confettis sont terminés
        }
        
        // Fonction pour animer le stickman qui pleure
        function faireStickmanPleurer() {
            // Positionner le stickman au milieu du chemin
            stickman.setAttribute('transform', 'translate(400, 200)');
            
            // Les bras couvrent le visage pour "pleurer"
            armLeft.setAttribute('x1', '0');
            armLeft.setAttribute('y1', '-60');
            armLeft.setAttribute('x2', '-10');
            armLeft.setAttribute('y2', '-100');  // Vers le visage
            
            armRight.setAttribute('x1', '0');
            armRight.setAttribute('y1', '-60');
            armRight.setAttribute('x2', '10');
            armRight.setAttribute('y2', '-100');  // Vers le visage
            
            // Jambes tremblantes / fléchies par désespoir
            let tremblementJambes = setInterval(() => {
                const tremblementGauche = Math.random() * 3 - 1.5;
                const tremblementDroit = Math.random() * 3 - 1.5;
                
                legLeft.setAttribute('x2', (-20 + tremblementGauche).toString());
                legRight.setAttribute('x2', (20 + tremblementDroit).toString());
            }, 100);
            
            // Changer le visage pour un visage triste
            // D'abord, trouver le sourire et le supprimer
            const sourire = document.querySelector('#stickman path[d^="M-10,-90"]');
            if (sourire) {
                sourire.remove();
            }
            
            // Créer une bouche triste
            const boucheTristeSVG = document.createElementNS("http://www.w3.org/2000/svg", "path");
            boucheTristeSVG.setAttribute('d', 'M-10,-95 Q0,-100 10,-95');
            boucheTristeSVG.setAttribute('stroke', 'black');
            boucheTristeSVG.setAttribute('stroke-width', '2');
            boucheTristeSVG.setAttribute('fill', 'none');
            stickman.appendChild(boucheTristeSVG);
            
            // Ajouter des gouttes de larmes
            const tete = document.querySelector('#stickman circle');
            const svgElement = tete.ownerSVGElement;
            
            // Larmes qui tombent du visage (plus petites)
            setInterval(() => {
                const larme = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                const coteLarme = Math.random() < 0.5 ? -8 : 8;  // Larme gauche ou droite
                larme.setAttribute('cx', coteLarme.toString());
                larme.setAttribute('cy', '-95');  // Position initiale près des yeux
                larme.setAttribute('r', '2');
                larme.setAttribute('fill', '#00a2ff');
                larme.style.opacity = '0.8';
                stickman.appendChild(larme);
                
                // Animation de la larme qui tombe
                let posY = -95;
                const animationLarme = setInterval(() => {
                    posY += 2;
                    larme.setAttribute('cy', posY.toString());
                    
                    // Supprimer la larme quand elle atteint le sol
                    if (posY > 0) {
                        clearInterval(animationLarme);
                        larme.remove();
                    }
                }, 50);
            }, 500);  // Une nouvelle larme toutes les 500ms
        }
        
        // Fonction pour faire tomber des larmes de l'écran
        function faireTomberLarmes() {
            // Créer un conteneur pour les larmes
            const larmesContainer = document.createElement('div');
            larmesContainer.id = 'larmes-container';
            larmesContainer.style.position = 'fixed';
            larmesContainer.style.top = '0';
            larmesContainer.style.left = '0';
            larmesContainer.style.width = '100vw';
            larmesContainer.style.height = '100vh';
            larmesContainer.style.pointerEvents = 'none';
            larmesContainer.style.zIndex = '9999';
            document.body.appendChild(larmesContainer);
            
            // Créer des gouttes de larmes
            const larmesCount = 200; // Nombre de larmes
            const blueColors = ['#0088ff', '#00a2ff', '#0076d6', '#005cbf', '#0047a0'];
            
            for (let i = 0; i < larmesCount; i++) {
                setTimeout(() => {
                    const larme = document.createElement('div');
                    larme.className = 'larme';
                    
                    // Position aléatoire sur toute la largeur
                    const startPosX = Math.random() * window.innerWidth;
                    
                    // Propriétés CSS
                    larme.style.position = 'fixed';
                    larme.style.left = startPosX + 'px';
                    larme.style.top = '-50px';
                    larme.style.backgroundColor = blueColors[Math.floor(Math.random() * blueColors.length)];
                    larme.style.opacity = '0.7';
                    larme.style.zIndex = '9999';
                    
                    // Forme de goutte d'eau
                    const size = Math.random() * 8 + 4; // Taille plus petite
                    larme.style.width = size + 'px';
                    larme.style.height = size * 1.5 + 'px'; // Plus haute que large
                    larme.style.borderRadius = '50% 50% 50% 50% / 60% 60% 40% 40%'; // Forme de goutte
                    larme.style.transform = 'rotate(30deg)'; // Rotation légère
                    
                    // Animation de chute
                    const fallDuration = Math.random() * 4 + 2; // 2-6 secondes
                    larme.style.animation = `fall ${fallDuration}s linear forwards`;
                    
                    // Délai aléatoire
                    const delay = Math.random() * 20; // Étalé sur 20 secondes
                    larme.style.animationDelay = `${delay}s`;
                    
                    larmesContainer.appendChild(larme);
                    
                    // Supprimer la larme après son animation
                    setTimeout(() => {
                        larme.remove();
                    }, (fallDuration + delay) * 1000 + 500);
                }, Math.random() * 1000); // Démarrage échelonné
            }
            
            // Ajouter le style CSS pour l'animation de chute
            const styleElement = document.createElement('style');
            styleElement.textContent = `
                @keyframes fall {
                    0% { 
                        transform: translateY(0) rotate(30deg); 
                        opacity: 0.7;
                    }
                    80% {
                        opacity: 0.7;
                    }
                    100% { 
                        transform: translateY(100vh) rotate(30deg); 
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(styleElement);
        }
    });
  </script>
</body>
</html> 