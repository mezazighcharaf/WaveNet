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
          <span><i class="fas fa-leaf"></i> <?php echo $_SESSION['points']; ?> points</span>
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
                <button id="btnAccomplir" class="btn btn-primary">Étape accomplie</button>
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

  <script src="/Projet_Web/assets/js/script.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const stickman = document.getElementById('stickman');
        const legLeft = document.getElementById('leg-left');
        const legRight = document.getElementById('leg-right');
        const armLeft = document.getElementById('arm-left');
        const armRight = document.getElementById('arm-right');
        const btnAccomplir = document.getElementById('btnAccomplir');
        const tooltip = document.getElementById('tooltip');
        
        // Récupérer l'étape en cours depuis la base de données (si connecté)
        <?php if ($_SESSION['user_id'] !== 'demo_user' && $defiEnCours): ?>
        let etapeActuelle = <?php 
        try {
            // Requête plus spécifique pour s'assurer que nous obtenons la bonne étape pour ce défi particulier
            $query = "SELECT Etape_En_Cours FROM utilisateur WHERE Id_Utilisateur = ? AND Defi_En_Cours = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $_SESSION['user_id']);
            $stmt->bindParam(2, $defiId);
            $stmt->execute();
            $etapeData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($etapeData && isset($etapeData['Etape_En_Cours'])) {
                echo intval($etapeData['Etape_En_Cours']);
            } else {
                echo 0;
            }
        } catch (PDOException $e) {
            echo 0;
        }
        ?>;
        console.log("Étape actuelle récupérée de la BD:", etapeActuelle);
        <?php elseif ($defiTermine): ?>
        // Si le défi est terminé, positionner à la fin
        let etapeActuelle = etapes.length - 1;
        console.log("Défi terminé, étape positionnée à la fin:", etapeActuelle);
        <?php else: ?>
        let etapeActuelle = 0;
        console.log("Nouvel utilisateur ou défi non commencé, étape mise à 0");
        <?php endif; ?>
        
        // Désactiver le bouton si le défi est terminé
        <?php if ($defiTermine): ?>
        if (btnAccomplir) {
            btnAccomplir.disabled = true;
            btnAccomplir.textContent = "Défi accompli";
        }
        <?php endif; ?>
        
        // Fonction pour initialiser la position du stickman et colorier les segments parcourus
        function initialiserStickman() {
            console.log("Initialisation du stickman à l'étape:", etapeActuelle);
            
            // Positionner le stickman à la position correspondant à son étape
            if (etapeActuelle > 0 && etapeActuelle < etapes.length) {
                // Positionner à l'étape actuelle
                stickman.setAttribute('transform', `translate(${etapes[etapeActuelle]}, 200)`);
                
                // Colorier les segments parcourus
                reinitialiserChemins(); // D'abord effacer toutes les colorations
                
                // Colorier tous les segments jusqu'à l'étape actuelle
                const nbSegmentsAColorier = etapeActuelle > 1 ? etapeActuelle - 1 : 0;
                
                for (let i = 0; i < nbSegmentsAColorier; i++) {
                    if (i < pathSegments.length) {
                        pathSegments[i].setAttribute('stroke', '#4CAF50'); // Vert
                        pathSegments[i].setAttribute('stroke-width', '4'); // Un peu plus épais
                    }
                }
            } else if (etapeActuelle >= etapes.length) {
                // Positionner à la fin si toutes les étapes sont terminées
                stickman.setAttribute('transform', `translate(${etapes[etapes.length-1]}, 200)`);
                
                // Colorier tous les segments
                for (let i = 0; i < pathSegments.length; i++) {
                    pathSegments[i].setAttribute('stroke', '#4CAF50');
                    pathSegments[i].setAttribute('stroke-width', '4');
                }
            }
        }
        
        // Sauvegarder l'étape en cours dans la base de données
        function sauvegarderEtape(etape) {
            <?php if ($_SESSION['user_id'] !== 'demo_user'): ?>
            console.log("Sauvegarde de l'étape:", etape, "pour le défi ID:", <?php echo $defiId; ?>);
            
            // Utiliser fetch pour envoyer l'étape au serveur
            fetch('sauvegarder_etape.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'etape=' + etape + '&defi_id=<?php echo $defiId; ?>'
            })
            .then(response => response.json())
            .then(data => {
                console.log('Progression sauvegardée:', data);
                
                // Afficher notification de points gagnés
                if (data.success && data.points > 0) {
                    afficherNotificationPoints(data.points);
                    
                    // Mettre à jour tous les compteurs de points visibles sur la page
                    fetch('get_points_utilisateur.php')
                        .then(response => response.json())
                        .then(dataPoints => {
                            if (dataPoints.success) {
                                const pointsCounters = document.querySelectorAll('.user-info span:first-child');
                                pointsCounters.forEach(counter => {
                                    counter.innerHTML = `<i class=\"fas fa-leaf\"></i> ${dataPoints.points} points`;
                                });
                            }
                        });
                }
            })
            .catch(error => {
                console.error('Erreur lors de la sauvegarde:', error);
            });
            <?php endif; ?>
        }
        
        // Fonction pour afficher une notification de points gagnés
        function afficherNotificationPoints(points) {
            const notification = document.createElement('div');
            notification.className = 'points-notification';
            notification.innerHTML = `<i class="fas fa-leaf"></i> +${points} points!`;
            notification.style.position = 'fixed';
            notification.style.top = '20%';
            notification.style.left = '50%';
            notification.style.transform = 'translate(-50%, -50%)';
            notification.style.backgroundColor = 'rgba(76, 175, 80, 0.9)';
            notification.style.color = 'white';
            notification.style.padding = '15px 25px';
            notification.style.borderRadius = '10px';
            notification.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.2)';
            notification.style.zIndex = '10000';
            notification.style.fontSize = '20px';
            notification.style.fontWeight = 'bold';
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.5s, transform 0.5s';
            
            document.body.appendChild(notification);
            
            // Animation d'apparition
            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translate(-50%, -50%) scale(1.1)';
            }, 10);
            
            // Animation de disparition
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translate(-50%, -50%) scale(0.9)';
                
                // Supprimer l'élément après la fin de l'animation
                setTimeout(() => {
                    notification.remove();
                }, 500);
            }, 3000);
        }
        
        // Vérification si le temps est écoulé (date de fin du défi dépassée)
        const dateFinDefi = new Date("<?php echo $defi['Date_Fin']; ?>");
        const dateActuelle = new Date();
        const tempsEcoule = dateActuelle > dateFinDefi;
        
        // Si le temps est écoulé, configurer l'état "temps écoulé"
        if (tempsEcoule) {
            console.log("Temps écoulé pour ce défi!");
            
            // Désactiver les boutons
            btnAccomplir.disabled = true;
            
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
                    let content = `<h4 class='tooltip-content-title'>${etape.title}</h4>`;
                    content += `<p class='tooltip-content-desc'>${etape.description}</p>`;
                    content += `<div class='tooltip-content-meta'>`;
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
        
        let enMouvement = false;
        let successCelebrated = false;
        let intervalMarche = null;
        let intervalCelebration = null;
        
        // Initialiser la position du stickman au chargement
        initialiserStickman();
        
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
            
            // Vérifier si le défi est terminé
            <?php if ($defiTermine): ?>
            console.log("Déplacement impossible - défi déjà accompli !");
            return;
            <?php endif; ?>
            
            // Ne pas permettre de revenir en arrière
            if (vers < etapeActuelle) {
                console.log("Impossible de revenir en arrière !");
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
                    
                    // Sauvegarder la progression
                    sauvegarderEtape(etapeActuelle);
                    
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
                    btnAccomplir.disabled = (etapeActuelle === etapes.length - 1);
                    
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
        btnAccomplir.addEventListener('click', function() {
            if (!enMouvement && etapeActuelle < etapes.length - 1) {
                deplacerStickman(etapeActuelle + 1);
            }
        });
        
        // Positionner le stickman à son étape actuelle au chargement
        if (etapeActuelle > 0) {
            // Colorier tous les segments jusqu'à l'étape actuelle
            for (let i = 0; i < etapeActuelle - 1; i++) {
                if (i < pathSegments.length) {
                    pathSegments[i].setAttribute('stroke', '#4CAF50');
                    pathSegments[i].setAttribute('stroke-width', '4');
                }
            }
            
            // Positionner le stickman
            const position = etapeActuelle < etapes.length ? etapes[etapeActuelle] : etapes[0];
            stickman.setAttribute('transform', `translate(${position}, 200)`);
            
            // Désactiver le bouton si on est à la dernière étape
            btnAccomplir.disabled = (etapeActuelle === etapes.length - 1);
            
            // Si c'est l'étape finale, mettre le stickman en mode célébration
            if (etapeActuelle === etapes.length - 1) {
                // Lever les bras en l'air
                armLeft.setAttribute('x1', '0');
                armLeft.setAttribute('y1', '-60');
                armLeft.setAttribute('x2', '-15');
                armLeft.setAttribute('y2', '-85');
                
                armRight.setAttribute('x1', '0');
                armRight.setAttribute('y1', '-60');
                armRight.setAttribute('x2', '15');
                armRight.setAttribute('y2', '-85');
            }
        }
        
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
        
        // Appeler la fonction d'initialisation au chargement
        initialiserStickman();
        
        // Remplacer l'ancien code de positionnement initial avec notre nouvelle fonction
        // ce code était à la fin du script, nous l'avons remplacé par l'appel ci-dessus
    });
  </script>
</body>
</html> 