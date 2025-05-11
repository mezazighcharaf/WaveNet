<?php
  // Initializing session if needed
  session_start();
  
  // Include defi controller for frontoffice
  require_once __DIR__ . '/../../controller/FrontofficeDefiController.php';
  require_once __DIR__ . '/../../model/Database.php';
  
  // Initialize controller
  $controller = new FrontofficeDefiController();
  
  // Créer une instance de la classe Database
  $database = new Database();
  $db = $database->getConnection();
  
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
  
  // Vérifier si l'utilisateur a déjà participé à ce défi (défi terminé)
  $defiTermine = false;
  if ($_SESSION['user_id'] !== 'demo_user') {
    try {
      $query = "SELECT * FROM participation WHERE Id_Utilisateur = ? AND Id_Defi = ?";
      $stmt = $db->prepare($query);
      $stmt->bindParam(1, $_SESSION['user_id']);
      $stmt->bindParam(2, $defiId);
      $stmt->execute();
      
      $defiTermine = ($stmt->rowCount() > 0);
    } catch (PDOException $e) {
      // Gérer l'erreur
    }
  }
  
  // Vérifier si l'utilisateur a ce défi comme défi en cours
  $defiEnCours = false;
  if ($_SESSION['user_id'] !== 'demo_user' && !$defiTermine) {
    try {
      // Utilisation de l'instance Database déjà créée
      // $db est déjà défini au début du fichier
      
      $query = "SELECT Defi_En_Cours FROM utilisateur WHERE Id_Utilisateur = ? AND Defi_En_Cours = ?";
      $stmt = $db->prepare($query);
      $stmt->bindParam(1, $_SESSION['user_id']);
      $stmt->bindParam(2, $defiId);
      $stmt->execute();
      
      $defiEnCours = ($stmt->rowCount() > 0);
    } catch (PDOException $e) {
      // Gérer l'erreur
    }
  }
  
  // Correction : Initialiser $etapes ici pour éviter l'erreur plus bas
  require_once __DIR__ . '/../../controller/EtapeController.php';
  $etapeController = new EtapeController();
  $etapes = $etapeController->getEtapesByDefi($defi['Id_Defi']);
  if (!is_array($etapes)) $etapes = [];
  $nombreEtapes = count($etapes);

  // Détection du temps écoulé côté PHP
  $isTempsEcoule = (strtotime(date('Y-m-d')) > strtotime($defi['Date_Fin']));
  $isDefiComplet = (isset($defiComplet) && $defiComplet) || $defiTermine;
  $isDefiAvenir = ($defi['Statut_D'] === 'À venir' || strtotime(date('Y-m-d')) < strtotime($defi['Date_Debut']));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $defi['Titre_D']; ?> - Urbaverse</title>
  <link rel="stylesheet" href="/Projet_Web/assets/css/frontoffice.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <!-- HEADER -->
  <header class="main-header">
    <div class="container">
      <div class="logo">
        <a href="index.php">
          <img src="/Projet_Web/assets/img/logo.jpg" alt="Logo Urbaverse">
          <span class="logo-title--white">Urbaverse</span>
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
          <span><i class="fas fa-leaf"></i> <span class="points-value"><?php echo $_SESSION['points']; ?></span> points</span>
        <?php else: ?>
          <span><i class="fas fa-leaf"></i> 0 points</span>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['username']) && $_SESSION['user_id'] !== 'demo_user'): ?>
          <span><i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?></span>
          <a href="logout.php" class="logout-link" title="Déconnexion"><i class="fas fa-sign-out-alt"></i></a>
        <?php else: ?>
          <a href="login.php" class="no-underline"><span><i class="fas fa-user"></i> Connexion</span></a>
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
            <div class="participation-cta">
                <h3>Participer au défi</h3>
                <p>Vous êtes prêt à relever ce défi ? Participez maintenant et gagnez des points pour chaque étape réussie !</p>
                
                <?php if ($_SESSION['user_id'] === 'demo_user'): ?>
                    <a href="login.php" class="btn-participate">Connectez-vous pour participer</a>
                <?php elseif ($defiTermine): ?>
                    <div class="btn-participate btn-participate--success">
                        <i class="fas fa-trophy"></i> Défi relevé avec succès !
                    </div>
                <?php elseif ($defiEnCours): ?>
                    <?php
                    // Vérifier si le défi est terminé (toutes les étapes complétées)
                    $query = "SELECT Etape_En_Cours FROM utilisateur WHERE Id_Utilisateur = ? AND Defi_En_Cours = ?";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $_SESSION['user_id']);
                    $stmt->bindParam(2, $defiId);
                    $stmt->execute();
                    $userDefi = $stmt->fetch(PDO::FETCH_ASSOC);
                    $defiComplet = ($userDefi && $userDefi['Etape_En_Cours'] >= count($etapes));
                    ?>
                    
                    <?php if ($defiComplet): ?>
                        <div class="btn-participate btn-participate--success">
                            <i class="fas fa-trophy"></i> Défi relevé avec succès !
                        </div>
                    <?php else: ?>
                        <a href="quitter_defi.php?id=<?php echo $defiId; ?>" class="btn-quit">Quitter ce défi</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="participer_defi.php?id=<?php echo $defiId; ?>" class="btn-participate">Participer à ce défi</a>
                <?php endif; ?>
            </div>
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
          <!-- Stickman Animation Container -->
          <div class="stickman-animation-container">
            <!-- Stickman Animation Frame -->
            <div class="stickman-frame">
                <svg id="parcours-defi" width="900" height="300" viewBox="0 0 900 300">
                    <!-- Points fixes de départ et d'arrivée -->
                    <?php
                      // Calculer les segments et points en fonction du nombre d'étapes
                      $segments = array();
                      
                      // Segments fixes
                      if ($nombreEtapes == 1) {
                        // S'il n'y a qu'une étape, un seul segment entre début et fin
                        echo '<line id="path-segment-0" x1="120" y1="200" x2="740" y2="200" stroke="black" stroke-width="3"/>';
                        echo '<circle cx="120" cy="200" r="4" fill="black" class="point-hover" data-point="0" data-title="Départ" data-details="Départ du défi"/>';
                        echo '<circle cx="740" cy="200" r="4" fill="black" class="point-hover" data-point="1" data-title="Arrivée" data-details="Arrivée du défi"/>';
                      } 
                      else if ($nombreEtapes == 2) {
                        // S'il y a deux étapes, on partage en deux segments uniformes
                        echo '<line id="path-segment-0" x1="120" y1="200" x2="430" y2="200" stroke="black" stroke-width="3"/>';
                        echo '<line id="path-segment-1" x1="430" y1="200" x2="740" y2="200" stroke="black" stroke-width="3"/>';
                        echo '<circle cx="120" cy="200" r="4" fill="black" class="point-hover" data-point="0" data-title="Départ" data-details="Départ du défi"/>';
                        $etape = $etapes[0] ?? [];
                        $details = htmlspecialchars(
                          'Titre : '.($etape['Titre_E'] ?? '')."\n".
                          'Description : '.($etape['Description_E'] ?? '')."\n".
                          'Objectif : '.($etape['Objectif_E'] ?? '')."\n".
                          'Points : '.($etape['Points_E'] ?? '')
                        );
                        echo '<circle cx="430" cy="200" r="4" fill="black" class="point-hover" data-point="1" data-title="Étape 1" data-details="'.$details.'"/>';
                        echo '<circle cx="740" cy="200" r="4" fill="black" class="point-hover" data-point="2" data-title="Arrivée" data-details="Arrivée du défi"/>';
                      }
                      else {
                        // Par défaut, on utilise 3 segments (comportement original)
                        echo '<line id="path-segment-0" x1="120" y1="200" x2="360" y2="200" stroke="black" stroke-width="3"/>';
                        echo '<line id="path-segment-1" x1="360" y1="200" x2="600" y2="200" stroke="black" stroke-width="3"/>';
                        echo '<line id="path-segment-2" x1="600" y1="200" x2="740" y2="200" stroke="black" stroke-width="3"/>';
                        echo '<circle cx="120" cy="200" r="4" fill="black" class="point-hover" data-point="0" data-title="Départ" data-details="Départ du défi"/>';
                        for ($i = 0; $i < $nombreEtapes; $i++) {
                          $x = 360 + 240 * $i; // positions intermédiaires
                          $etape = $etapes[$i] ?? [];
                          $stepNum = $i + 1;
                          $details = htmlspecialchars(
                            'Titre : '.($etape['Titre_E'] ?? '')."\n".
                            'Description : '.($etape['Description_E'] ?? '')."\n".
                            'Objectif : '.($etape['Objectif_E'] ?? '')."\n".
                            'Points : '.($etape['Points_E'] ?? '')
                          );
                          echo '<circle cx="'.$x.'" cy="200" r="4" fill="black" class="point-hover" data-point="'.$stepNum.'" data-title="Étape '.$stepNum.'" data-details="'.$details.'"/>';
                        }
                        echo '<circle cx="740" cy="200" r="4" fill="black" class="point-hover" data-point="'.($nombreEtapes+1).'" data-title="Arrivée" data-details="Arrivée du défi"/>';
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
                    <g id="stickman" transform="translate(120, 200)">
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
                <button id="btn-accomplir" class="btn btn-primary">Étape accomplie</button>
                </div>
            
            <!-- Conteneur pour l'infobulle -->
            <div id="tooltip" class="tooltip tooltip-absolute"></div>
            
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
          <img src="/Projet_Web/assets/img/logo.jpg" alt="Logo Urbaverse" class="footer-logo-img">
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

  <!-- MODALE DE VALIDATION PAR IMAGE -->
  <div id="modal-validation-image" class="modal" style="display:none;">
    <div class="modal-content">
      <span id="close-modal" style="float:right;cursor:pointer;">&times;</span>
      <h3>Preuve d'accomplissement</h3>
      <button id="btn-capture-photo" class="btn-capture-photo">Prendre une photo</button>
      <div id="upload-area" class="upload-area">
        <input type="file" id="input-upload-image" accept="image/*" style="display:none;">
        <label for="input-upload-image" id="upload-label" class="upload-label">
          <span class="upload-btn">Choisir une image</span>
          <span class="upload-or">ou glissez votre fichier ici</span>
        </label>
      </div>
      <div id="preview-container">
        <video id="video" width="340" height="260" autoplay style="display:none;"></video>
        <canvas id="canvas" width="340" height="260" style="display:none;"></canvas>
        <img id="img-preview" src="" style="max-width:340px;display:none;">
      </div>
      <button id="btn-verifier" disabled>Vérifier</button>
      <div id="result-validation" style="margin-top:10px;"></div>
    </div>
  </div>

  <!-- Toast de succès pour validation d'étape -->
  <div id="toast-success" style="display:none;position:fixed;bottom:40px;left:50%;transform:translateX(-50%);background:#4CAF50;color:white;padding:16px 32px;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.15);font-size:18px;z-index:99999;">Étape validée !</div>

  <!-- Message défi terminé -->
  <?php if ((isset($defiComplet) && $defiComplet) || $defiTermine): ?>
    <div id="defi-termine-message" style="
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: rgba(44, 62, 80, 0.98);
      color: #fff;
      padding: 56px 80px 40px 80px;
      border-radius: 32px;
      font-size: 3rem;
      font-weight: 900;
      z-index: 10001;
      box-shadow: 0 12px 48px 0 rgba(44,62,80,0.25), 0 0 32px 0 #FFD70099;
      text-align: center;
      letter-spacing: 2.5px;
      text-shadow: 0 2px 16px #222, 0 0 12px #FFD700;
      animation: popin 0.7s cubic-bezier(.68,-0.55,.27,1.55);
    ">
      <span style="font-size:4rem;display:block;">🏆</span>
      Défi Terminé !
      <div style="font-size:1.3rem;font-weight:400;margin-top:18px;opacity:0.92;letter-spacing:1px;">Félicitations, tu as relevé ce défi avec succès !</div>
    </div>
    <style>
      @keyframes popin {
        0% { transform: translate(-50%, -50%) scale(0.7); opacity: 0; }
        70% { transform: translate(-50%, -50%) scale(1.1); opacity: 1; }
        100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
      }
    </style>
  <?php endif; ?>

  <!-- Message temps écoulé -->
  <?php if ($isTempsEcoule && !$isDefiComplet): ?>
    <div id="temps-ecoule-message" style="
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: #d32f2f;
      color: #fff;
      padding: 48px 64px;
      border-radius: 32px;
      font-size: 2.5rem;
      font-weight: bold;
      z-index: 10001;
      box-shadow: 0 8px 32px rgba(200,0,0,0.18);
      text-align: center;
      letter-spacing: 2px;
      text-shadow: 0 2px 16px #222, 0 0 12px #fff;
      animation: popin 0.7s cubic-bezier(.68,-0.55,.27,1.55);
    ">
      <span style="font-size:3rem;display:block;">😢</span>
      Temps écoulé !<br>
      <span style="font-size:1.2rem;font-weight:400;">Tu n'as pas pu terminer ce défi à temps...</span>
    </div>
    <style>
      @keyframes popin {
        0% { transform: translate(-50%, -50%) scale(0.7); opacity: 0; }
        70% { transform: translate(-50%, -50%) scale(1.1); opacity: 1; }
        100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
      }
    </style>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Désactiver le bouton d'accomplissement si présent
        var btnAccomplir = document.getElementById('btn-accomplir');
        if (btnAccomplir) {
          btnAccomplir.disabled = true;
          btnAccomplir.textContent = 'Défi expiré';
        }
        // Animation stickman triste + pluie de larmes
        var stickman = document.getElementById('stickman');
        var armLeft = document.getElementById('arm-left');
        var armRight = document.getElementById('arm-right');
        var legLeft = document.getElementById('leg-left');
        var legRight = document.getElementById('leg-right');
        function faireStickmanPleurer() {
          if (!stickman) return;
          stickman.setAttribute('transform', 'translate(400, 200)');
          armLeft.setAttribute('x1', '0');
          armLeft.setAttribute('y1', '-60');
          armLeft.setAttribute('x2', '-10');
          armLeft.setAttribute('y2', '-100');
          armRight.setAttribute('x1', '0');
          armRight.setAttribute('y1', '-60');
          armRight.setAttribute('x2', '10');
          armRight.setAttribute('y2', '-100');
          setInterval(() => {
            const tremblementGauche = Math.random() * 3 - 1.5;
            const tremblementDroit = Math.random() * 3 - 1.5;
            legLeft.setAttribute('x2', (-20 + tremblementGauche).toString());
            legRight.setAttribute('x2', (20 + tremblementDroit).toString());
          }, 100);
          // Bouche triste
          var sourire = stickman.querySelector('path[d^="M-10,-90"]');
          if (sourire) sourire.remove();
          var boucheTristeSVG = document.createElementNS("http://www.w3.org/2000/svg", "path");
          boucheTristeSVG.setAttribute('d', 'M-10,-95 Q0,-100 10,-95');
          boucheTristeSVG.setAttribute('stroke', 'black');
          boucheTristeSVG.setAttribute('stroke-width', '2');
          boucheTristeSVG.setAttribute('fill', 'none');
          stickman.appendChild(boucheTristeSVG);
          // Larmes qui tombent du visage
          setInterval(() => {
            const larme = document.createElementNS("http://www.w3.org/2000/svg", "circle");
            const coteLarme = Math.random() < 0.5 ? -8 : 8;
            larme.setAttribute('cx', coteLarme.toString());
            larme.setAttribute('cy', '-95');
            larme.setAttribute('r', '2');
            larme.setAttribute('fill', '#00a2ff');
            larme.style.opacity = '0.8';
            stickman.appendChild(larme);
            let posY = -95;
            const animationLarme = setInterval(() => {
              posY += 2;
              larme.setAttribute('cy', posY.toString());
              if (posY > 0) {
                clearInterval(animationLarme);
                larme.remove();
              }
            }, 50);
          }, 500);
        }
        function faireTomberLarmes() {
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
          const larmesCount = 200;
          const blueColors = ['#0088ff', '#00a2ff', '#0076d6', '#005cbf', '#0047a0'];
          for (let i = 0; i < larmesCount; i++) {
            setTimeout(() => {
              const larme = document.createElement('div');
              larme.className = 'larme';
              const startPosX = Math.random() * window.innerWidth;
              larme.style.position = 'fixed';
              larme.style.left = startPosX + 'px';
              larme.style.top = '-50px';
              larme.style.backgroundColor = blueColors[Math.floor(Math.random() * blueColors.length)];
              larme.style.opacity = '0.7';
              larme.style.zIndex = '9999';
              const size = Math.random() * 8 + 4;
              larme.style.width = size + 'px';
              larme.style.height = size * 1.5 + 'px';
              larme.style.borderRadius = '50% 50% 50% 50% / 60% 60% 40% 40%';
              larme.style.transform = 'rotate(30deg)';
              const fallDuration = Math.random() * 4 + 2;
              larme.style.animation = `fall ${fallDuration}s linear forwards`;
              const delay = Math.random() * 20;
              larme.style.animationDelay = `${delay}s`;
              larmesContainer.appendChild(larme);
              setTimeout(() => {
                larme.remove();
              }, (fallDuration + delay) * 1000 + 500);
            }, Math.random() * 1000);
          }
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
        // Lancer les animations tristes après un court délai pour s'assurer que le SVG est chargé
        setTimeout(function() {
          faireStickmanPleurer();
          faireTomberLarmes();
        }, 300);
      });
    </script>
  <?php endif; ?>

  <!-- Message défi à venir -->
  <?php if ($isDefiAvenir): ?>
    <div id="defi-avenir-message" style="
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: rgba(44, 62, 80, 0.97);
      color: #fff;
      padding: 48px 64px;
      border-radius: 24px;
      font-size: 2.5rem;
      font-weight: bold;
      z-index: 10001;
      box-shadow: 0 8px 32px rgba(44,62,80,0.18);
      text-align: center;
      letter-spacing: 2px;
      text-shadow: 0 2px 16px #222, 0 0 12px #fff;
      animation: popin 0.7s cubic-bezier(.68,-0.55,.27,1.55);
    ">
      <span style="font-size:3rem;display:block;">⏳</span>
      Défi à venir<br>
      <span style="font-size:1.2rem;font-weight:400;">Ce défi n'a pas encore commencé.<br>Il sera disponible à partir du <b><?php echo date('d/m/Y', strtotime($defi['Date_Debut'])); ?></b>.</span>
    </div>
    <style>
      @keyframes popin {
        0% { transform: translate(-50%, -50%) scale(0.7); opacity: 0; }
        70% { transform: translate(-50%, -50%) scale(1.1); opacity: 1; }
        100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
      }
    </style>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Désactiver le bouton d'accomplissement si présent
        var btnAccomplir = document.getElementById('btn-accomplir');
        if (btnAccomplir) {
          btnAccomplir.disabled = true;
          btnAccomplir.textContent = 'Défi à venir';
        }
      });
    </script>
  <?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.18.0/dist/tf.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet@2.1.0"></script>
  <script>
    // Variables globales pour stickman.js (doivent être globales, donc var)
    var stickman = document.getElementById('stickman');
    var legLeft = document.getElementById('leg-left');
    var legRight = document.getElementById('leg-right');
    var armLeft = document.getElementById('arm-left');
    var armRight = document.getElementById('arm-right');
    // Tableau des positions X des points dans le SVG
    var etapes = [120]; // Position du point de départ
    <?php
    if ($nombreEtapes == 1) {
        echo "etapes.push(840);"; // Position finale après le drapeau
    } else if ($nombreEtapes == 2) {
        echo "etapes.push(430, 840);"; // Étape 1, position finale
    } else {
        for ($i = 0; $i < $nombreEtapes; $i++) {
            $x = 360 + 240 * $i;
            echo "etapes.push($x);";
        }
        echo "etapes.push(840);"; // Position finale
    }
    ?>
    var pathSegments = [
      document.getElementById('path-segment-0'),
      document.getElementById('path-segment-1'),
      document.getElementById('path-segment-2')
    ];
    var enMouvement = false;
    var successCelebrated = false;
    var intervalMarche = null;
    var intervalCelebration = null;
    // Positionne le stickman DIRECTEMENT sur le premier point d'étape au chargement
    var etapeActuelle = 0;
    if (stickman && etapes && etapes.length > 0) {
      stickman.setAttribute('transform', `translate(${etapes[0]}, 200)`);
    }
  </script>
  <script src="/Projet_Web/assets/js/stickman.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Variables globales
        let stream = null;
        let isCapturing = false;
        let mobilenetModel = null;
        let modelReady = false;
        
        // Récupérer les éléments de la modale
        const modal = document.getElementById('modal-validation-image');
        const closeModal = document.getElementById('close-modal');
        const btnCapturePhoto = document.getElementById('btn-capture-photo');
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const imgPreview = document.getElementById('img-preview');
        const btnVerifier = document.getElementById('btn-verifier');
        const uploadArea = document.getElementById('upload-area');
        const inputUpload = document.getElementById('input-upload-image');
        const resultValidation = document.getElementById('result-validation');

        // Fonction pour charger le modèle
        async function loadModel() {
            try {
                console.log('Chargement du modèle MobileNet...');
                mobilenetModel = await mobilenet.load();
                modelReady = true;
                console.log('Modèle MobileNet chargé avec succès !');
                const resultValidation = document.getElementById('result-validation');
                if (resultValidation) {
                    resultValidation.textContent = '';
                }
            } catch (err) {
                console.error('Erreur lors du chargement du modèle:', err);
                const resultValidation = document.getElementById('result-validation');
                if (resultValidation) {
                    resultValidation.textContent = 'Erreur lors du chargement du modèle.';
                }
            }
        }

        // Charger le modèle au démarrage
        loadModel();

        // Fonction pour réinitialiser la prévisualisation
        function resetPreview() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            video.style.display = 'none';
            canvas.style.display = 'none';
            imgPreview.style.display = 'none';
            imgPreview.src = '';
            btnVerifier.disabled = true;
            resultValidation.textContent = '';
            btnCapturePhoto.textContent = 'Prendre une photo';
            isCapturing = false;
        }

        // Gestion de la capture photo
        if (btnCapturePhoto) {
            btnCapturePhoto.addEventListener('click', async function() {
                if (!isCapturing) {
                    try {
                        stream = await navigator.mediaDevices.getUserMedia({ video: true });
                        video.srcObject = stream;
                        video.style.display = 'block';
                        canvas.style.display = 'none';
                        imgPreview.style.display = 'none';
                        btnVerifier.disabled = true;
                        btnCapturePhoto.textContent = 'Capturer';
                        isCapturing = true;
                    } catch (err) {
                        console.error('Erreur accès caméra:', err);
                        alert('Impossible d\'accéder à la caméra : ' + err.message);
                    }
                } else {
                    const context = canvas.getContext('2d');
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const imageData = canvas.toDataURL('image/png');
                    imgPreview.src = imageData;
                    imgPreview.style.display = 'block';
                    video.style.display = 'none';
                    canvas.style.display = 'none';
                    btnVerifier.disabled = false;
                    
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                        stream = null;
                    }
                    
                    btnCapturePhoto.textContent = 'Prendre une photo';
                    isCapturing = false;
                }
            });
        }

        // Gestionnaire d'événement pour le bouton Vérifier
        if (btnVerifier) {
            btnVerifier.addEventListener('click', async function() {
                if (!modelReady) {
                    resultValidation.textContent = 'Le modèle n\'est pas prêt. Veuillez patienter.';
                    return;
                }
                if (!imgPreview.src || imgPreview.style.display !== 'block') {
                    resultValidation.textContent = 'Aucune image à vérifier.';
                    return;
                }

                btnVerifier.textContent = 'Vérification en cours...';
                btnVerifier.disabled = true;
                resultValidation.textContent = '';

                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.src = imgPreview.src;

                await new Promise((resolve, reject) => {
                    img.onload = resolve;
                    img.onerror = reject;
                });

                const predictions = await mobilenetModel.classify(img);

                // Récupère le nom de l'étape courante
                let nomEtape = '';
                if (typeof currentStepIndex !== 'undefined' && etapesData[currentStepIndex]) {
                    nomEtape = etapesData[currentStepIndex].title + ' ' + (etapesData[currentStepIndex].description || '');
                } else {
                    nomEtape = document.querySelector('.tooltip-content-title')?.textContent || '';
                }
                let nomDefi = "<?php echo addslashes($defi['Titre_D']); ?>";

                // Traduction et mots-clés
                const traductions = {
                    'arbre': ['tree'],
                    'fleur': ['flower'],
                    'plante': ['plant'],
                    'graines': ['seed', 'seeds'],
                    'planter': ['planting', 'sow', 'sowing'],
                    'semis': ['seedling', 'seedlings'],
                    'terre': ['soil', 'earth', 'ground'],
                    'eau': ['water', 'watering'],
                    'vélo': ['bicycle', 'bike'],
                    'poubelle': ['trash', 'bin', 'garbage'],
                    'recycler': ['recycle', 'recycling'],
                    'forêt': ['forest'],
                    'feuille': ['leaf', 'leaves']
                };

                // Affiche les prédictions pour debug
                let debug = '<b>Prédictions du modèle :</b><ul>';
                predictions.slice(0, 3).forEach(pred => {
                    debug += `<li>${pred.className} (${(pred.probability*100).toFixed(1)}%)</li>`;
                });
                debug += '</ul>';

                // Génère la liste des mots-clés à valider
                const motsEtape = nomEtape.toLowerCase().split(/\s+/);
                let motsAnglais = [];
                motsEtape.forEach(mot => {
                    if (traductions[mot]) motsAnglais = motsAnglais.concat(traductions[mot]);
                });
                const motsGeneriques = ['plant', 'seed', 'garden', 'person', 'activity', 'tree', 'flower', 'nature', 'outdoor', 'soil', 'pot', 'hand', 'grow', 'green'];

                // On compare les prédictions avec tous les mots français, anglais, génériques
                const isValid = predictions.some(pred => {
                    const label = pred.className.toLowerCase();
                    if (motsEtape.some(mot => mot.length > 3 && label.includes(mot))) return true;
                    if (motsAnglais.some(mot => label.includes(mot))) return true;
                    if (motsGeneriques.some(mot => label.includes(mot))) return true;
                    if (label.includes(nomDefi.toLowerCase())) return true;
                    return false;
                });

                if (isValid) {
                    resultValidation.innerHTML = debug + '<span style="color:green;font-weight:700;">✔️ Étape validée automatiquement !</span>';
                    if (modal) modal.style.display = 'none';
                    const toast = document.getElementById('toast-success');
                    if (toast) {
                        toast.style.display = 'block';
                        setTimeout(() => { toast.style.display = 'none'; }, 2000);
                    }
                    if (etapeActuelle < etapes.length - 1 && typeof deplacerStickman === 'function') {
                        deplacerStickman(etapeActuelle + 1);
                        // Appel AJAX pour sauvegarder l'étape et récupérer les points
                        fetch('sauvegarder_etape.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'etape=' + (etapeActuelle + 1) + '&defi_id=<?php echo $defiId; ?>'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && typeof afficherNotificationPoints === 'function') {
                                afficherNotificationPoints(data.points);
                            }
                        });
                    }
                    if (typeof resetPreview === 'function') resetPreview();
                } else {
                    resultValidation.innerHTML = debug + '<span style="color:#c62828;font-weight:700;">❌ Image non conforme à l\'étape.</span>';
                }

                btnVerifier.textContent = 'Vérifier';
                btnVerifier.disabled = false;
            });
        }

        // Ouvrir la modale quand on clique sur "Étape accomplie"
        const btnAccomplir = document.getElementById('btn-accomplir');
        if (btnAccomplir) {
            console.log('Bouton Étape accomplie trouvé, événement attaché');
            btnAccomplir.addEventListener('click', function(e) {
                e.preventDefault();
                if (modal) {
                    modal.style.display = 'block';
                    resetPreview();
                }
            });
        } else {
            alert('Le bouton Étape accomplie n\'a pas été trouvé dans le DOM !');
        }

        // Fermer la modale
        if (closeModal) {
            closeModal.addEventListener('click', function() {
                modal.style.display = 'none';
                resetPreview();
            });
        }

        // Fermer la modale en cliquant en dehors
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
                resetPreview();
            }
        });

        // Gestion de l'upload d'image
        if (inputUpload) {
            inputUpload.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const file = e.target.files[0];
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        imgPreview.src = e.target.result;
                        imgPreview.style.display = 'block';
                        video.style.display = 'none';
                        canvas.style.display = 'none';
                        btnVerifier.disabled = false;
                    };
                    
                    reader.readAsDataURL(file);
                }
            });
        }

        // Gestion du drag & drop
        if (uploadArea) {
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                
                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    const file = e.dataTransfer.files[0];
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        imgPreview.src = e.target.result;
                        imgPreview.style.display = 'block';
                        video.style.display = 'none';
                        canvas.style.display = 'none';
                        btnVerifier.disabled = false;
                    };
                    
                    reader.readAsDataURL(file);
                }
            });
        }

        // Fonction pour créer et afficher les confettis et le message de succès (depuis défis_complet.php)
        function celebrerSucces() {
            console.log('Célébration appelée !');
            document.getElementById('message-succes').style.opacity = "1";
            const confettiContainer = document.getElementById('confetti-container');
            confettiContainer.innerHTML = '';
            const confettiCount = 700;
            const colors = ['#f00', '#0f0', '#00f', '#ff0', '#0ff', '#f0f', '#fd0', '#0fd', '#f83', '#8f3', '#3f8', '#83f', '#f38'];
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                const startPosX = Math.random() * window.innerWidth;
                confetti.style.left = startPosX + 'px';
                confetti.style.top = '-50px';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                const size = Math.random() * 15 + 5;
                confetti.style.width = size + 'px';
                confetti.style.height = size + 'px';
                const shapeNum = Math.floor(Math.random() * 3);
                if (shapeNum === 0) {
                    confetti.style.borderRadius = '50%';
                } else if (shapeNum === 1) {
                    confetti.style.borderRadius = '0';
                } else {
                    confetti.style.borderRadius = '0';
                    confetti.style.transform = 'rotate(45deg)';
                }
                const fallDuration = Math.random() * 5 + 3;
                confetti.style.animation = `fall ${fallDuration}s linear forwards`;
                const delay = Math.random() * 5;
                confetti.style.animationDelay = `${delay}s`;
                confettiContainer.appendChild(confetti);
                setTimeout(() => {
                    confetti.remove();
                }, (fallDuration + delay) * 1000 + 500);
            }
            setTimeout(() => {
                document.getElementById('message-succes').style.opacity = "0";
            }, 5000);
            setTimeout(() => {
                confettiContainer.innerHTML = '';
            }, 15000);
        }
        window.celebrerSucces = celebrerSucces;
        window.successCelebrated = false;

        // Désactiver le bouton si le défi est terminé ou complet
        var btnAccomplir = document.getElementById('btn-accomplir');
        var defiComplet = <?php echo isset($defiComplet) && $defiComplet ? 'true' : 'false'; ?>;
        var defiTermine = <?php echo $defiTermine ? 'true' : 'false'; ?>;
        if (btnAccomplir && (defiComplet || defiTermine)) {
            btnAccomplir.disabled = true;
            btnAccomplir.textContent = 'Défi déjà relevé';
        }
        // Placer le stickman à la fin si défi terminé
        if ((defiComplet || defiTermine) && typeof etapes !== 'undefined' && stickman) {
            stickman.setAttribute('transform', `translate(${etapes[etapes.length-1]}, 200)`);
        }

        // Gestion du temps écoulé (stickman qui pleure + pluie de larmes)
        var isTempsEcoule = <?php echo ($isTempsEcoule && !$isDefiComplet) ? 'true' : 'false'; ?>;
        if (isTempsEcoule) {
            var btnAccomplir = document.getElementById('btn-accomplir');
            if (btnAccomplir) {
                btnAccomplir.disabled = true;
                btnAccomplir.textContent = 'Défi expiré';
            }
            // Fonction pour faire pleurer le stickman
            function faireStickmanPleurer() {
                if (!stickman) return;
                stickman.setAttribute('transform', 'translate(400, 200)');
                armLeft.setAttribute('x1', '0');
                armLeft.setAttribute('y1', '-60');
                armLeft.setAttribute('x2', '-10');
                armLeft.setAttribute('y2', '-100');
                armRight.setAttribute('x1', '0');
                armRight.setAttribute('y1', '-60');
                armRight.setAttribute('x2', '10');
                armRight.setAttribute('y2', '-100');
                // Jambes tremblantes
                setInterval(() => {
                    const tremblementGauche = Math.random() * 3 - 1.5;
                    const tremblementDroit = Math.random() * 3 - 1.5;
                    legLeft.setAttribute('x2', (-20 + tremblementGauche).toString());
                    legRight.setAttribute('x2', (20 + tremblementDroit).toString());
                }, 100);
                // Bouche triste
                const sourire = document.querySelector('#stickman path[d^="M-10,-90"]');
                if (sourire) sourire.remove();
                const boucheTristeSVG = document.createElementNS("http://www.w3.org/2000/svg", "path");
                boucheTristeSVG.setAttribute('d', 'M-10,-95 Q0,-100 10,-95');
                boucheTristeSVG.setAttribute('stroke', 'black');
                boucheTristeSVG.setAttribute('stroke-width', '2');
                boucheTristeSVG.setAttribute('fill', 'none');
                stickman.appendChild(boucheTristeSVG);
                // Larmes qui tombent du visage
                setInterval(() => {
                    const larme = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                    const coteLarme = Math.random() < 0.5 ? -8 : 8;
                    larme.setAttribute('cx', coteLarme.toString());
                    larme.setAttribute('cy', '-95');
                    larme.setAttribute('r', '2');
                    larme.setAttribute('fill', '#00a2ff');
                    larme.style.opacity = '0.8';
                    stickman.appendChild(larme);
                    let posY = -95;
                    const animationLarme = setInterval(() => {
                        posY += 2;
                        larme.setAttribute('cy', posY.toString());
                        if (posY > 0) {
                            clearInterval(animationLarme);
                            larme.remove();
                        }
                    }, 50);
                }, 500);
            }
            // Fonction pour faire tomber des larmes sur l'écran
            function faireTomberLarmes() {
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
                const larmesCount = 200;
                const blueColors = ['#0088ff', '#00a2ff', '#0076d6', '#005cbf', '#0047a0'];
                for (let i = 0; i < larmesCount; i++) {
                    setTimeout(() => {
                        const larme = document.createElement('div');
                        larme.className = 'larme';
                        const startPosX = Math.random() * window.innerWidth;
                        larme.style.position = 'fixed';
                        larme.style.left = startPosX + 'px';
                        larme.style.top = '-50px';
                        larme.style.backgroundColor = blueColors[Math.floor(Math.random() * blueColors.length)];
                        larme.style.opacity = '0.7';
                        larme.style.zIndex = '9999';
                        const size = Math.random() * 8 + 4;
                        larme.style.width = size + 'px';
                        larme.style.height = size * 1.5 + 'px';
                        larme.style.borderRadius = '50% 50% 50% 50% / 60% 60% 40% 40%';
                        larme.style.transform = 'rotate(30deg)';
                        const fallDuration = Math.random() * 4 + 2;
                        larme.style.animation = `fall ${fallDuration}s linear forwards`;
                        const delay = Math.random() * 20;
                        larme.style.animationDelay = `${delay}s`;
                        larmesContainer.appendChild(larme);
                        setTimeout(() => {
                            larme.remove();
                        }, (fallDuration + delay) * 1000 + 500);
                    }, Math.random() * 1000);
                }
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
            // Lancer les animations tristes
            faireStickmanPleurer();
            faireTomberLarmes();
        }
    });
  </script>
  <script>
    // Affichage notification points (version défis_complet.php)
    function afficherNotificationPoints(points) {
        const notification = document.createElement('div');
        notification.className = 'points-notification';
        notification.innerHTML = `<i class="fas fa-leaf"></i> <b>Bravo ! Vous avez gagné +${points} points !</b>`;
        document.body.appendChild(notification);
        setTimeout(() => { notification.style.opacity = '1'; }, 10);
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => { notification.remove(); }, 500);
        }, 3000);
    }
  </script>
  <script>
    // Fonction pour mettre à jour l'affichage des points
    function updatePointsDisplay() {
        fetch('/Projet_Web/view/frontoffice/get_points_utilisateur.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const pointsElement = document.querySelector('.points-value');
                    if (pointsElement) {
                        pointsElement.textContent = data.points;
                    }
                }
            })
            .catch(error => console.error('Erreur lors de la récupération des points:', error));
    }

    // Mettre à jour l'affichage des points après avoir gagné des points
    function showPointsNotification(points) {
        const notification = document.createElement('div');
        notification.className = 'points-notification';
        notification.textContent = `+${points} points !`;
        document.body.appendChild(notification);

        // Afficher la notification
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translate(-50%, -50%)';
        }, 100);

        // Mettre à jour l'affichage des points
        updatePointsDisplay();

        // Supprimer la notification après l'animation
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translate(-50%, -60%)';
            setTimeout(() => notification.remove(), 500);
        }, 2000);
    }

    // Modifier la fonction sauvegarderProgression pour utiliser la nouvelle notification
    function sauvegarderProgression(etape) {
        fetch('/Projet_Web/view/frontoffice/sauvegarder_etape.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `etape=${etape}&defi_id=${defiId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.points > 0) {
                    showPointsNotification(data.points);
                }
            } else {
                console.error('Erreur:', data.message);
            }
        })
        .catch(error => console.error('Erreur:', error));
    }
  </script>
</body>
</html> 