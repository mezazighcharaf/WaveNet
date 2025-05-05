<?php
// File: /WaveNet/views/frontoffice/account_activity.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { 
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page.";
    header('Location: /WaveNet/views/frontoffice/login.php');
    exit;
}

// Vérifier si l'authentification à deux facteurs a été complétée
require_once '../../controller/UserController.php';
UserController::check2FAVerified();

// Informations de la page
$pageTitle = 'Activité du compte';
$activePage = 'account_activity';

// Connexion à la base de données
require_once '../../views/includes/config.php';
$db = connectDB();
if (!$db) {
    die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
}

// Inclure les modèles et fonctions nécessaires
require_once '../../models/Utilisateur.php';
require_once '../../models/security_functions.php';

// Récupérer l'ID de l'utilisateur connecté
$userId = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur
$userData = null;
try {
    $userData = Utilisateur::findById($db, $userId);
    if (!$userData) {
        throw new Exception("Utilisateur non trouvé");
    }
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des données utilisateur: " . $e->getMessage());
}

// Récupérer le statut 2FA
$twofa_enabled = false;
try {
    $stmt = $db->prepare("SELECT twofa_enabled FROM UTILISATEUR WHERE id_utilisateur = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $twofa_enabled = !empty($result['twofa_enabled']);
} catch (Exception $e) {
    error_log("Erreur lors de la récupération du statut 2FA: " . $e->getMessage());
}

// Vérifier si l'email est vérifié (pour le moment, on va simuler cette vérification)
$email_verified = false;
try {
    // Vérifier si la colonne email_verified existe
    $columnCheck = $db->query("SHOW COLUMNS FROM UTILISATEUR LIKE 'email_verified'");
    if ($columnCheck->rowCount() === 0) {
        // Créer la colonne si elle n'existe pas
        $db->exec("ALTER TABLE UTILISATEUR ADD COLUMN email_verified TINYINT(1) DEFAULT 0");
    }
    
    // Récupérer le statut de vérification d'email
    $stmt = $db->prepare("SELECT email_verified FROM UTILISATEUR WHERE id_utilisateur = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $email_verified = !empty($result['email_verified']);
} catch (Exception $e) {
    error_log("Erreur lors de la vérification du statut d'email: " . $e->getMessage());
}

// Traiter la demande de vérification d'email
if (isset($_POST['send_verification_email'])) {
    try {
        // Générer un token de vérification
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Vérifier si la table email_verification existe
        $tableCheck = $db->query("SHOW TABLES LIKE 'email_verification'");
        if ($tableCheck->rowCount() === 0) {
            // Créer la table si elle n'existe pas
            $db->exec("CREATE TABLE email_verification (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_utilisateur INT NOT NULL,
                token VARCHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id_utilisateur) ON DELETE CASCADE
            )");
        }
        
        // Supprimer les anciens tokens pour cet utilisateur
        $stmt = $db->prepare("DELETE FROM email_verification WHERE id_utilisateur = ?");
        $stmt->execute([$userId]);
        
        // Insérer le nouveau token
        $stmt = $db->prepare("INSERT INTO email_verification (id_utilisateur, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $token, $expires]);
        
        // Simuler l'envoi d'un email (dans une véritable implémentation, envoyez un email réel ici)
        // Note: Ce code simule uniquement le processus pour la démonstration
        $_SESSION['verification_email_sent'] = true;
        $_SESSION['success_message'] = "Un email de vérification a été envoyé à votre adresse. Veuillez vérifier votre boîte de réception.";
        
        header('Location: /WaveNet/views/frontoffice/account_activity.php');
        exit;
    } catch (Exception $e) {
        error_log("Erreur lors de la génération du token de vérification: " . $e->getMessage());
        $_SESSION['error_message'] = "Une erreur est survenue. Veuillez réessayer.";
    }
}

// Récupérer l'historique des connexions
$connexions = [];
try {
    $stmt = $db->prepare("SELECT * FROM connexion_logs 
                         WHERE id_utilisateur = ? 
                         ORDER BY date_connexion DESC 
                         LIMIT 50");
    $stmt->execute([$userId]);
    $connexions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Créer un enregistrement de test dans les logs
    if (empty($connexions)) {
        try {
            $stmt = $db->prepare("INSERT INTO connexion_logs (id_utilisateur, ip_address, user_agent, success)
                                VALUES (?, ?, ?, 1)");
            $stmt->execute([$userId, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
            
            // Rafraîchir la liste des connexions
            $stmt = $db->prepare("SELECT * FROM connexion_logs 
                                WHERE id_utilisateur = ? 
                                ORDER BY date_connexion DESC 
                                LIMIT 50");
            $stmt->execute([$userId]);
            $connexions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de l'insertion d'un log de test: " . $e->getMessage());
        }
    }
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération de l'historique des connexions: " . $e->getMessage());
}

// Récupérer les messages d'erreur/succès de la session
$errorMessage = '';
$successMessage = '';
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Inclure l'en-tête
require_once '../includes/userHeader.php';
?>
<!-- Lien de navigation de secours - À SUPPRIMER APRÈS LE DÉBOGAGE -->
<div style="text-align: center; margin: 10px 0; padding: 10px; background-color: #f0f0f0;">
    <a href="/WaveNet/views/frontoffice/userDashboard.php">Tableau de bord</a> | 
    <a href="/WaveNet/views/frontoffice/editProfile.php">Profil</a> | 
    <b>Activité du compte</b>
</div>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | WaveNet</title>
    <link rel="stylesheet" href="../../views/assets/css/style11.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Élément de fond -->
    <div class="page-background"></div>
    
    <!-- HERO SECTION -->
    <section class="hero" style="min-height: 30vh;">
      <div class="hero-container">
        <div class="hero-content">
          <h1 class="hero-title">Activité de votre compte</h1>
          <p class="hero-text">Consultez l'historique des connexions et l'activité récente de votre compte pour assurer sa sécurité.</p>
        </div>
      </div>
    </section>
    
    <!-- CONTENT -->
    <div class="container" style="margin-top: -3rem; position: relative; z-index: 10; margin-bottom: 3rem;">
      <div class="card" style="padding: 2rem; max-width: 1100px; margin: 0 auto; background-color: var(--white); border-radius: var(--border-radius-lg); box-shadow: var(--shadow-md);">
        
        <!-- Messages d'erreur/succès -->
        <?php if (!empty($errorMessage)): ?>
          <div style="padding: 1rem; background-color: #FFF5F5; color: #F56565; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
          </div>
        <?php endif; ?>
        
        <?php if (!empty($successMessage)): ?>
          <div style="padding: 1rem; background-color: #F0FFF4; color: #48BB78; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
            <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
          </div>
        <?php endif; ?>
        
        <!-- Statut de sécurité du compte -->
        <div style="margin-bottom: 3rem;">
          <h2 style="margin-bottom: 1.5rem; color: var(--dark-green); font-size: 1.8rem;">Statut de sécurité</h2>
          
          <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
            <!-- Statut 2FA -->
            <div style="background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); padding: 1.5rem;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div style="font-size: 1.5rem; color: var(--accent-green);">
                  <i class="fas fa-shield-alt"></i>
                </div>
                <?php if ($twofa_enabled): ?>
                  <span style="display: inline-block; background-color: rgba(72, 187, 120, 0.1); color: #48BB78; padding: 0.3rem 0.8rem; border-radius: var(--border-radius-sm); font-size: 0.85rem;">
                    <i class="fas fa-check-circle"></i> Activé
                  </span>
                <?php else: ?>
                  <span style="display: inline-block; background-color: rgba(245, 101, 101, 0.1); color: #F56565; padding: 0.3rem 0.8rem; border-radius: var(--border-radius-sm); font-size: 0.85rem;">
                    <i class="fas fa-times-circle"></i> Désactivé
                  </span>
                <?php endif; ?>
              </div>
              <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 0.5rem;">Authentification à deux facteurs</h3>
              <p style="color: var(--text-color); margin-bottom: 1rem;">
                <?php if ($twofa_enabled): ?>
                  Votre compte est protégé par l'authentification à deux facteurs. Cette méthode renforce significativement la sécurité de votre compte.
                <?php else: ?>
                  L'authentification à deux facteurs ajoute une couche de sécurité supplémentaire à votre compte en exigeant un code temporaire en plus de votre mot de passe.
                <?php endif; ?>
              </p>
              <a href="/WaveNet/controller/UserController.php?action=gerer2FA" class="btn btn-sm" style="display: inline-block; margin-top: 0.5rem; padding: 0.5rem 1rem; background-color: var(--accent-green); color: white; border-radius: var(--border-radius-sm); text-decoration: none;">
                <?php echo $twofa_enabled ? 'Gérer la 2FA' : 'Activer la 2FA'; ?>
              </a>
            </div>
            
            <!-- Statut Email -->
            <div style="background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); padding: 1.5rem;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div style="font-size: 1.5rem; color: var(--accent-green);">
                  <i class="fas fa-envelope"></i>
                </div>
                <?php if ($email_verified): ?>
                  <span style="display: inline-block; background-color: rgba(72, 187, 120, 0.1); color: #48BB78; padding: 0.3rem 0.8rem; border-radius: var(--border-radius-sm); font-size: 0.85rem;">
                    <i class="fas fa-check-circle"></i> Vérifié
                  </span>
                <?php else: ?>
                  <span style="display: inline-block; background-color: rgba(245, 101, 101, 0.1); color: #F56565; padding: 0.3rem 0.8rem; border-radius: var(--border-radius-sm); font-size: 0.85rem;">
                    <i class="fas fa-times-circle"></i> Non vérifié
                  </span>
                <?php endif; ?>
              </div>
              <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 0.5rem;">Vérification de l'email</h3>
              <p style="color: var(--text-color); margin-bottom: 1rem;">
                <?php if ($email_verified): ?>
                  Votre adresse email a été vérifiée. Vous recevrez toutes les notifications importantes concernant votre compte.
                <?php else: ?>
                  Vérifiez votre adresse email pour recevoir les notifications importantes et renforcer la sécurité de votre compte.
                <?php endif; ?>
              </p>
              <?php if (!$email_verified): ?>
                <a href="/WaveNet/controller/UserController.php?action=sendEmailVerification" class="btn btn-sm" style="display: inline-block; margin-top: 0.5rem; padding: 0.5rem 1rem; background-color: var(--accent-green); color: white; border-radius: var(--border-radius-sm); text-decoration: none; border: none; cursor: pointer;">
                  Envoyer un email de vérification
                </a>
              <?php endif; ?>
            </div>
            
            <!-- Date dernier changement de mot de passe -->
            <div style="background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); padding: 1.5rem;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div style="font-size: 1.5rem; color: var(--accent-green);">
                  <i class="fas fa-key"></i>
                </div>
                <?php
                // Récupérer la date du dernier changement de mot de passe
                $lastPasswordChange = null;
                try {
                    $stmt = $db->prepare("SELECT MAX(date_changement) as last_change FROM password_history WHERE id_utilisateur = ?");
                    $stmt->execute([$userId]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $lastPasswordChange = $result['last_change'];
                } catch (Exception $e) {
                    error_log("Erreur lors de la récupération de la date de changement de mot de passe: " . $e->getMessage());
                }
                
                $passwordStatus = 'unknown';
                $daysAgo = null;
                if ($lastPasswordChange) {
                    $lastChangeDate = new DateTime($lastPasswordChange);
                    $now = new DateTime();
                    $diff = $now->diff($lastChangeDate);
                    $daysAgo = $diff->days;
                    
                    if ($daysAgo > 180) { // Plus de 6 mois
                        $passwordStatus = 'danger';
                    } elseif ($daysAgo > 90) { // Plus de 3 mois
                        $passwordStatus = 'warning';
                    } else {
                        $passwordStatus = 'good';
                    }
                }
                ?>
                
                <?php if ($passwordStatus === 'good'): ?>
                  <span style="display: inline-block; background-color: rgba(72, 187, 120, 0.1); color: #48BB78; padding: 0.3rem 0.8rem; border-radius: var(--border-radius-sm); font-size: 0.85rem;">
                    <i class="fas fa-check-circle"></i> À jour
                  </span>
                <?php elseif ($passwordStatus === 'warning'): ?>
                  <span style="display: inline-block; background-color: rgba(237, 137, 54, 0.1); color: #ED8936; padding: 0.3rem 0.8rem; border-radius: var(--border-radius-sm); font-size: 0.85rem;">
                    <i class="fas fa-exclamation-circle"></i> À renouveler
                  </span>
                <?php elseif ($passwordStatus === 'danger'): ?>
                  <span style="display: inline-block; background-color: rgba(245, 101, 101, 0.1); color: #F56565; padding: 0.3rem 0.8rem; border-radius: var(--border-radius-sm); font-size: 0.85rem;">
                    <i class="fas fa-exclamation-triangle"></i> Trop ancien
                  </span>
                <?php else: ?>
                  <span style="display: inline-block; background-color: rgba(160, 174, 192, 0.1); color: #A0AEC0; padding: 0.3rem 0.8rem; border-radius: var(--border-radius-sm); font-size: 0.85rem;">
                    <i class="fas fa-question-circle"></i> Inconnu
                  </span>
                <?php endif; ?>
              </div>
              <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 0.5rem;">Mot de passe</h3>
              <p style="color: var(--text-color); margin-bottom: 1rem;">
                <?php if ($lastPasswordChange): ?>
                  Dernier changement il y a <?php echo $daysAgo; ?> jour<?php echo $daysAgo > 1 ? 's' : ''; ?>.
                  <?php if ($passwordStatus === 'danger'): ?>
                    Il est fortement recommandé de changer votre mot de passe régulièrement.
                  <?php elseif ($passwordStatus === 'warning'): ?>
                    Pensez à changer votre mot de passe prochainement.
                  <?php else: ?>
                    Votre mot de passe est récent.
                  <?php endif; ?>
                <?php else: ?>
                  Aucun historique de changement de mot de passe disponible.
                <?php endif; ?>
              </p>
              <a href="/WaveNet/views/frontoffice/editProfile.php" class="btn btn-sm" style="display: inline-block; margin-top: 0.5rem; padding: 0.5rem 1rem; background-color: var(--accent-green); color: white; border-radius: var(--border-radius-sm); text-decoration: none;">
                Changer de mot de passe
              </a>
            </div>
          </div>
        </div>
        
        <!-- Dernières connexions -->
        <div style="margin-bottom: 3rem;">
          <h2 style="margin-bottom: 1.5rem; color: var(--dark-green); font-size: 1.8rem;">Historique des connexions</h2>
          
          <?php if (empty($connexions)): ?>
            <div style="padding: 2rem; text-align: center; background-color: var(--gray-100); border-radius: var(--border-radius);">
              <p style="color: var(--gray-500);">Aucun historique de connexion disponible.</p>
            </div>
          <?php else: ?>
            <div style="overflow-x: auto;">
              <table style="width: 100%; border-collapse: collapse;">
                <thead>
                  <tr style="background-color: var(--gray-100); text-align: left;">
                    <th style="padding: 1rem; border-bottom: 1px solid var(--gray-200);">Date</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--gray-200);">IP</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--gray-200);">Localisation</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--gray-200);">Appareil/Navigateur</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--gray-200);">Statut</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($connexions as $connexion): ?>
                    <tr style="border-bottom: 1px solid var(--gray-200);">
                      <td style="padding: 1rem;">
                        <?php echo date('d/m/Y H:i', strtotime($connexion['date_connexion'])); ?>
                      </td>
                      <td style="padding: 1rem;">
                        <?php echo htmlspecialchars($connexion['ip_address']); ?>
                      </td>
                      <td style="padding: 1rem;">
                        <?php 
                          $location = [];
                          if (!empty($connexion['city']) && $connexion['city'] != 'Unknown') {
                              $location[] = $connexion['city'];
                          }
                          if (!empty($connexion['country']) && $connexion['country'] != 'Unknown') {
                              $location[] = $connexion['country'];
                          }
                          echo !empty($location) ? htmlspecialchars(implode(', ', $location)) : 'Inconnue';
                        ?>
                      </td>
                      <td style="padding: 1rem;">
                        <?php 
                          $userAgent = $connexion['user_agent'] ?? '';
                          // Analyse simplifiée de l'agent utilisateur
                          $browser = '';
                          if (strpos($userAgent, 'Firefox') !== false) {
                              $browser = 'Firefox';
                          } elseif (strpos($userAgent, 'Chrome') !== false && strpos($userAgent, 'Edg') === false) {
                              $browser = 'Chrome';
                          } elseif (strpos($userAgent, 'Edg') !== false) {
                              $browser = 'Edge';
                          } elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
                              $browser = 'Safari';
                          } else {
                              $browser = 'Navigateur';
                          }
                          
                          $device = '';
                          if (strpos($userAgent, 'Mobile') !== false) {
                              $device = 'Mobile';
                          } elseif (strpos($userAgent, 'Tablet') !== false) {
                              $device = 'Tablette';
                          } else {
                              $device = 'Ordinateur';
                          }
                          
                          echo "$device / $browser";
                        ?>
                      </td>
                      <td style="padding: 1rem;">
                        <?php if ($connexion['success']): ?>
                          <span style="display: inline-block; background-color: rgba(72, 187, 120, 0.1); color: #48BB78; padding: 0.3rem 0.8rem; border-radius: var(--border-radius-sm); font-size: 0.85rem;">
                            <i class="fas fa-check-circle"></i> Réussie
                          </span>
                        <?php else: ?>
                          <span style="display: inline-block; background-color: rgba(245, 101, 101, 0.1); color: #F56565; padding: 0.3rem 0.8rem; border-radius: var(--border-radius-sm); font-size: 0.85rem;">
                            <i class="fas fa-times-circle"></i> Échec
                            <?php if (!empty($connexion['failure_reason'])): ?>
                              (<?php echo htmlspecialchars($connexion['failure_reason']); ?>)
                            <?php endif; ?>
                          </span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            
            <!-- Carte des localisations si assez de données -->
            <?php 
              $validConnections = array_filter($connexions, function($conn) {
                  return !empty($conn['latitude']) && !empty($conn['longitude']) && 
                         $conn['latitude'] != 0 && $conn['longitude'] != 0;
              });
              
              if (count($validConnections) > 0): 
            ?>
            <div style="margin-top: 2rem;">
              <h3 style="margin-bottom: 1rem; color: var(--dark-green); font-size: 1.4rem;">Carte des connexions</h3>
              <div id="map" style="height: 400px; border-radius: var(--border-radius); margin-bottom: 1rem;"></div>
              <p style="color: var(--gray-500); font-size: 0.85rem; text-align: center;">
                La carte montre les localisations approximatives de vos connexions récentes.
              </p>
            </div>
            
            <!-- Script pour la carte -->
            <script>
              document.addEventListener('DOMContentLoaded', function() {
                // Vérifier si Leaflet est déjà chargé, sinon le charger
                if (typeof L === 'undefined') {
                  var link = document.createElement('link');
                  link.rel = 'stylesheet';
                  link.href = 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css';
                  document.head.appendChild(link);
                  
                  var script = document.createElement('script');
                  script.src = 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js';
                  script.onload = initMap;
                  document.head.appendChild(script);
                } else {
                  initMap();
                }
                
                function initMap() {
                  var map = L.map('map').setView([0, 0], 2);
                  
                  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                  }).addTo(map);
                  
                  var markers = [];
                  var bounds = L.latLngBounds();
                  
                  <?php foreach ($validConnections as $conn): ?>
                    var lat = <?php echo $conn['latitude']; ?>;
                    var lng = <?php echo $conn['longitude']; ?>;
                    var date = "<?php echo date('d/m/Y H:i', strtotime($conn['date_connexion'])); ?>";
                    var city = "<?php echo !empty($conn['city']) && $conn['city'] != 'Unknown' ? $conn['city'] : 'Inconnue'; ?>";
                    var country = "<?php echo !empty($conn['country']) && $conn['country'] != 'Unknown' ? $conn['country'] : 'Inconnu'; ?>";
                    var status = <?php echo $conn['success'] ? 'true' : 'false'; ?>;
                    
                    var marker = L.marker([lat, lng]).addTo(map);
                    marker.bindPopup(
                      "<strong>Date:</strong> " + date + "<br>" +
                      "<strong>Localisation:</strong> " + city + ", " + country + "<br>" +
                      "<strong>Statut:</strong> " + (status ? "Réussie" : "Échec")
                    );
                    
                    markers.push(marker);
                    bounds.extend([lat, lng]);
                  <?php endforeach; ?>
                  
                  if (markers.length > 0) {
                    map.fitBounds(bounds);
                  }
                }
              });
            </script>
            <?php endif; ?>
            
          <?php endif; ?>
        </div>
        
        <!-- Export des données RGPD -->
        <div id="rgpd" style="margin-bottom: 3rem;">
          <h2 style="margin-bottom: 1.5rem; color: var(--dark-green); font-size: 1.8rem;">Export de vos données personnelles (RGPD)</h2>
          
          <div style="background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); padding: 2rem;">
            <div style="margin-bottom: 1.5rem;">
              <h3 style="color: var(--dark-green); font-size: 1.4rem; margin-bottom: 1rem;">Télécharger vos données</h3>
              <p style="color: var(--text-color); margin-bottom: 1.5rem;">
                Conformément au Règlement Général sur la Protection des Données (RGPD), vous pouvez exporter toutes vos données personnelles stockées dans notre système. 
                L'export contient vos informations de profil, l'historique des connexions, les changements de mot de passe et les paramètres de sécurité.
              </p>
              
              <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
                <p style="margin-bottom: 0; color: var(--text-color); font-weight: 500;">Choisissez le format d'export :</p>
                
                <a href="/WaveNet/controller/ExportController.php?action=export&format=json" class="btn btn-sm" style="padding: 0.7rem 1.5rem; background-color: var(--accent-green); color: white; border-radius: var(--border-radius); text-decoration: none; display: inline-flex; align-items: center;">
                  <i class="fas fa-file-code" style="margin-right: 0.5rem;"></i> Format JSON
                </a>
                
                <a href="/WaveNet/controller/ExportController.php?action=export&format=pdf" class="btn btn-sm" style="padding: 0.7rem 1.5rem; background-color: var(--gray-500); color: white; border-radius: var(--border-radius); text-decoration: none; display: inline-flex; align-items: center;">
                  <i class="fas fa-file-pdf" style="margin-right: 0.5rem;"></i> Format PDF 
                </a>
              </div>
              
              <!-- Liste des données incluses dans l'export -->
              <div style="margin-top: 1.5rem; border: 1px solid var(--gray-200); border-radius: var(--border-radius); padding: 1.5rem;">
                <h4 style="font-size: 1.1rem; color: var(--dark-green); margin-bottom: 1rem;">Données incluses dans l'export :</h4>
                <ul style="list-style-type: none; padding-left: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 0.5rem 1.5rem;">
                  <li style="padding: 0.3rem 0; display: flex; align-items: center;">
                    <i class="fas fa-user" style="color: var(--accent-green); width: 20px; margin-right: 0.75rem;"></i>
                    <span>Informations du profil</span>
                  </li>
                  <li style="padding: 0.3rem 0; display: flex; align-items: center;">
                    <i class="fas fa-envelope" style="color: var(--accent-green); width: 20px; margin-right: 0.75rem;"></i>
                    <span>Statut de vérification d'email</span>
                  </li>
                  <li style="padding: 0.3rem 0; display: flex; align-items: center;">
                    <i class="fas fa-history" style="color: var(--accent-green); width: 20px; margin-right: 0.75rem;"></i>
                    <span>Historique des connexions</span>
                  </li>
                  <li style="padding: 0.3rem 0; display: flex; align-items: center;">
                    <i class="fas fa-shield-alt" style="color: var(--accent-green); width: 20px; margin-right: 0.75rem;"></i>
                    <span>Statut de sécurité 2FA</span>
                  </li>
                  <li style="padding: 0.3rem 0; display: flex; align-items: center;">
                    <i class="fas fa-key" style="color: var(--accent-green); width: 20px; margin-right: 0.75rem;"></i>
                    <span>Historique des mots de passe</span>
                  </li>
                  <li style="padding: 0.3rem 0; display: flex; align-items: center;">
                    <i class="fas fa-calendar-alt" style="color: var(--accent-green); width: 20px; margin-right: 0.75rem;"></i>
                    <span>Dates de création/modification</span>
                  </li>
                </ul>
              </div>
              
              <div style="background-color: var(--gray-100); padding: 1.5rem; border-radius: var(--border-radius); margin-top: 1.5rem;">
                <h4 style="font-size: 1.1rem; color: var(--dark-green); margin-bottom: 1rem;">Informations concernant vos données</h4>
                <ul style="list-style-type: none; padding-left: 0; margin: 0;">
                  <li style="padding: 0.5rem 0; display: flex; align-items: center;">
                    <i class="fas fa-check-circle" style="color: var(--accent-green); margin-right: 0.75rem;"></i>
                    <span>Ces données sont fournies dans un format lisible et couramment utilisé.</span>
                  </li>
                  <li style="padding: 0.5rem 0; display: flex; align-items: center;">
                    <i class="fas fa-check-circle" style="color: var(--accent-green); margin-right: 0.75rem;"></i>
                    <span>Vous pouvez demander la suppression de ces données en contactant notre service client.</span>
                  </li>
                  <li style="padding: 0.5rem 0; display: flex; align-items: center;">
                    <i class="fas fa-check-circle" style="color: var(--accent-green); margin-right: 0.75rem;"></i>
                    <span>L'export inclut uniquement vos données personnelles, conformément à la législation RGPD.</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Conseils de sécurité -->
        <div style="margin-bottom: 3rem;">
          <h2 style="margin-bottom: 1.5rem; color: var(--dark-green); font-size: 1.8rem;">Conseils de sécurité</h2>
          
          <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
            <div style="background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); padding: 1.5rem;">
              <div style="font-size: 2rem; color: var(--accent-green); margin-bottom: 1rem;">
                <i class="fas fa-shield-alt"></i>
              </div>
              <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 0.5rem;">Activez l'A2F</h3>
              <p style="color: var(--text-color);">L'authentification à deux facteurs ajoute une couche de sécurité supplémentaire à votre compte.</p>
              <a href="/WaveNet/controller/UserController.php?action=gerer2FA" class="btn btn-sm" style="display: inline-block; margin-top: 1rem; padding: 0.5rem 1rem; background-color: var(--accent-green); color: white; border-radius: var(--border-radius-sm); text-decoration: none;">
                <?php echo $twofa_enabled ? 'Gérer' : 'Activer'; ?>
              </a>
            </div>
            
            <div style="background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); padding: 1.5rem;">
              <div style="font-size: 2rem; color: var(--accent-green); margin-bottom: 1rem;">
                <i class="fas fa-key"></i>
              </div>
              <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 0.5rem;">Mot de passe fort</h3>
              <p style="color: var(--text-color);">Utilisez un mot de passe unique et complexe, et changez-le régulièrement.</p>
              <a href="/WaveNet/views/frontoffice/editProfile.php" class="btn btn-sm" style="display: inline-block; margin-top: 1rem; padding: 0.5rem 1rem; background-color: var(--accent-green); color: white; border-radius: var(--border-radius-sm); text-decoration: none;">
                Changer
              </a>
            </div>
            
            <div style="background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); padding: 1.5rem;">
              <div style="font-size: 2rem; color: var(--accent-green); margin-bottom: 1rem;">
                <i class="fas fa-envelope"></i>
              </div>
              <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 0.5rem;">Vérifiez votre email</h3>
              <p style="color: var(--text-color);">Assurez-vous que votre adresse email est à jour pour les notifications de sécurité.</p>
              <?php if (!$email_verified): ?>
                <a href="/WaveNet/controller/UserController.php?action=sendEmailVerification" class="btn btn-sm" style="display: inline-block; margin-top: 1rem; padding: 0.5rem 1rem; background-color: var(--accent-green); color: white; border-radius: var(--border-radius-sm); text-decoration: none; border: none; cursor: pointer;">
                  Vérifier
                </a>
              <?php else: ?>
                <a href="/WaveNet/views/frontoffice/editProfile.php" class="btn btn-sm" style="display: inline-block; margin-top: 1rem; padding: 0.5rem 1rem; background-color: var(--accent-green); color: white; border-radius: var(--border-radius-sm); text-decoration: none;">
                  Mettre à jour
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <!-- Bouton de retour -->
        <div style="text-align: center;">
          <a href="/WaveNet/views/frontoffice/userDashboard.php" class="btn" style="display: inline-block; padding: 0.8rem 1.5rem; background-color: var(--gray-200); color: var(--gray-700); border-radius: var(--border-radius); text-decoration: none; font-weight: 500;">
            <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i> Retour au tableau de bord
          </a>
        </div>
        
      </div>
    </div>
    
    <!-- JS pour les graphiques si nécessaire -->
    <script>
      // Script pour d'éventuelles visualisations
    </script>
</body>
</html>
