<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', __DIR__ . '/debug_dashboard.log');
ini_set('log_errors', 1);
error_log("Démarrage de userDashboard.php");
if (session_status() === PHP_SESSION_NONE) { 
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page.";
    header('Location: /WaveNet/views/frontoffice/login.php');
    exit;
}

// Vérifier si l'authentification à deux facteurs a été complétée
require_once '../../controller/UserController.php';
UserController::check2FAVerified();

$pageTitle = 'Tableau de bord';
$activePage = 'dashboard';
require_once '../../views/includes/config.php';
$db = connectDB();
if (!$db) {
    error_log("Erreur: Impossible d'établir une connexion à la base de données.");
    die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
}
require_once '../../models/Utilisateur.php';
require_once '../../models/Defi.php';
require_once '../../models/Transport.php';
require_once '../../models/Quartier.php';
require_once '../../models/security_functions.php';
require_once '../../controller/quartierC.php';
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_nom'] ?? 'Utilisateur';
$userEmail = $_SESSION['user_email'] ?? 'N/A';
$userLevel = $_SESSION['user_level'] ?? 'N/A';
try {
    $userDbData = Utilisateur::findById($db, $userId);
    if (!$userDbData) {
        error_log("Utilisateur avec ID $userId non trouvé dans la base de données.");
        $_SESSION = array();
        session_destroy();
        header("Location: /WaveNet/views/frontoffice/login.php?error=user_not_found");
        exit;
    }
    $idQuartier = $userDbData->getIdQuartier();
    $quartierName = 'Non défini';
    if ($idQuartier) {
        $quartierC = new quartierC();
        $quartierData = $quartierC->recupererQuartierparId($idQuartier);
        if ($quartierData) {
            $quartierName = $quartierData['nomq'];
        }
        $defisQuartier = Defi::getDefisByQuartier($db, $idQuartier);
        $defisCompletes = 0;
        $defisEnCours = 0;
        $defisEnCours = min(count($defisQuartier), 2); 
        $defisCompletes = count($defisQuartier) > 2 ? (count($defisQuartier) - 2) : 0; 
    }
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des données utilisateur: " . $e->getMessage());
    die("Une erreur est survenue lors de la récupération de vos données. Veuillez réessayer plus tard.");
}
$transports = [];
try {
    $transports = Transport::findByUserId($db, $userId);
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des données de transport réelles: " . $e->getMessage());
}
$transportStats = [
    'total_distance' => 0,
    'avg_eco_index' => 0,
    'co2_saved' => 0
];
if (!empty($transports)) {
    $totalEcoIndex = 0;
    $worstCaseEmissions = 0; 
    $actualEmissions = 0;   
    foreach ($transports as $transport) {
        $distance = floatval($transport['distance_parcourue']);
        $frequence = intval($transport['frequence']);
        $ecoIndex = floatval($transport['eco_index']);
        $transportStats['total_distance'] += $distance * $frequence;
        $totalEcoIndex += $ecoIndex;
        $worstCaseEmissions += $distance * $frequence * 0.150; 
        $emissionFactor = 0;
        if ($ecoIndex < 10) {
            $emissionFactor = (10 - $ecoIndex) / 7 * 0.150; 
        }
        $actualEmissions += $distance * $frequence * $emissionFactor;
    }
    $transportStats['avg_eco_index'] = $totalEcoIndex / count($transports);
    $transportStats['co2_saved'] = $worstCaseEmissions - $actualEmissions;
}
$userData = [
    'firstname' => $userDbData->getPrenom() ?? 'Inconnu',
    'lastname' => $userDbData->getNom() ?? 'Inconnu',
    'email' => $userDbData->getEmail() ?? 'Inconnu',
    'city' => '', 
    'district' => $quartierName,
    'interests' => '', 
    'newsletter' => 0, 
    'points' => $userDbData->getPointsVerts() ?? 0,
    'level' => $userDbData->getNiveau() ?? 'Explorateur',
    'challenges_completed' => $defisCompletes ?? 0,
    'challenges_in_progress' => $defisEnCours ?? 0,
    'district_rank' => 0, 
    'district_total' => 0, 
    'co2_saved' => round($transportStats['co2_saved']) 
];
$errorMessages = [];
if (isset($_SESSION['error_messages'])) {
    $errorMessages = $_SESSION['error_messages'];
    unset($_SESSION['error_messages']);
}
$successMessage = '';
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
require_once '../includes/userHeader.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | WaveNet</title>
    <link rel="stylesheet" href="../../views/assets/css/style11.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Élément de fond -->
    <div class="page-background"></div>
    <!-- DASHBOARD HERO SECTION -->
    <section class="hero" style="min-height: 40vh;">
      <div class="hero-container">
        <!-- Contenu textuel (côté gauche) -->
        <div class="hero-content">
          <h1 class="hero-title">Bienvenue, <span style="color: var(--accent-green);"><?php echo $userData['firstname']; ?></span></h1>
          <p class="hero-text">Votre tableau de bord pour suivre vos actions écologiques et contribuer à la transformation de votre quartier.</p>
        </div>
        <div class="hero-image-container">
          <img src="../../assets/img/ville.jpg" alt="Ville verte" class="hero-image">
        </div>
      </div>
    </section>
    <!-- DASHBOARD CONTENT -->
    <div class="container" style="margin-top: -3rem; position: relative; z-index: 10; margin-bottom: 3rem;">
      <div class="card" style="padding: 2rem; max-width: 1100px; margin: 0 auto; background-color: var(--white); border-radius: var(--border-radius-lg); box-shadow: var(--shadow-md);">
        <!-- Statistiques utilisateur -->
        <div style="margin-bottom: 3rem;">
          <h2 style="margin-bottom: 1.5rem; color: var(--dark-green); font-size: 1.8rem;">Mes statistiques</h2>
          <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 1.5rem;">
            <div style="flex: 1; min-width: 220px; background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); transition: transform var(--transition-speed), box-shadow var(--transition-speed);">
              <div style="padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.5rem; color: var(--accent-green); margin-bottom: 0.5rem;">
                  <i class="fas fa-leaf"></i>
                </div>
                <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 0.5rem;">Points Verts</h3>
                <div style="font-size: 2rem; font-weight: 700; color: var(--accent-green);"><?php echo $userData['points']; ?></div>
                <p style="color: var(--gray-500); margin-top: 0.5rem;">Niveau: <?php echo $userData['level']; ?></p>
              </div>
            </div>
            <div style="flex: 1; min-width: 220px; background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); transition: transform var(--transition-speed), box-shadow var(--transition-speed);">
              <div style="padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.5rem; color: var(--accent-green); margin-bottom: 0.5rem;">
                  <i class="fas fa-check-circle"></i>
                </div>
                <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 0.5rem;">Défis Complétés</h3>
                <div style="font-size: 2rem; font-weight: 700; color: var(--accent-green);"><?php echo $userData['challenges_completed']; ?></div>
                <p style="color: var(--gray-500); margin-top: 0.5rem;"><?php echo $userData['challenges_in_progress']; ?> en cours</p>
              </div>
            </div>
            <div style="flex: 1; min-width: 220px; background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); transition: transform var(--transition-speed), box-shadow var(--transition-speed);">
              <div style="padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.5rem; color: var(--accent-green); margin-bottom: 0.5rem;">
                  <i class="fas fa-trophy"></i>
                </div>
                <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 0.5rem;">Rang Quartier</h3>
                <div style="font-size: 2rem; font-weight: 700; color: var(--accent-green);"><?php echo $userData['district_rank']; ?><span style="font-size: 1.5rem; font-weight: 400;">/<?php echo $userData['district_total']; ?></span></div>
                <p style="color: var(--gray-500); margin-top: 0.5rem;">Quartier <?php echo $userData['district']; ?></p>
              </div>
            </div>
            <div style="flex: 1; min-width: 220px; background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); transition: transform var(--transition-speed), box-shadow var(--transition-speed);">
              <div style="padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.5rem; color: var(--accent-green); margin-bottom: 0.5rem;">
                  <i class="fas fa-cloud"></i>
                </div>
                <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 0.5rem;">CO2 Évité</h3>
                <div style="font-size: 2rem; font-weight: 700; color: var(--accent-green);"><?php echo $userData['co2_saved']; ?>kg</div>
                <p style="color: var(--gray-500); margin-top: 0.5rem;">Grâce à vos choix de transport</p>
              </div>
            </div>
          </div>
        </div>
        <!-- Défis en cours -->
        <div style="margin-bottom: 3rem;">
          <h2 style="margin-bottom: 1.5rem; color: var(--dark-green); font-size: 1.8rem;">Mes défis en cours</h2>
          <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 1.5rem;">
            <?php if (empty($defisQuartier)): ?>
              <div style="flex: 1 1 100%; text-align: center; padding: 2rem; background-color: var(--gray-100); border-radius: var(--border-radius);">
                <p style="color: var(--gray-500);">Aucun défi n'est disponible pour votre quartier actuellement.</p>
              </div>
            <?php else: ?>
              <?php 
                $counter = 0;
                foreach ($defisQuartier as $defi): 
                  if ($counter < 2): 
                    $counter++;
                    $progression = rand(10, 90);
                    $valeurActuelle = rand(5, 45);
                    $objectifTotal = rand(50, 100);
              ?>
                <div style="flex: 1 1 48%; min-width: 300px; background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); transition: transform var(--transition-speed), box-shadow var(--transition-speed);">
                  <div style="padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                      <h3 style="color: var(--dark-green); font-size: 1.2rem;"><?php echo htmlspecialchars($defi['Titre_D']); ?></h3>
                      <span style="background-color: rgba(76, 175, 80, 0.1); color: var(--accent-green); padding: 0.3rem 0.8rem; border-radius: var(--border-radius-sm); font-size: 0.85rem; font-weight: 600;">En cours</span>
                    </div>
                    <p style="margin-bottom: 1rem; color: var(--text-color);"><?php echo htmlspecialchars($defi['Description_D']); ?></p>
                    <div style="margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                      <span style="font-size: 0.9rem; color: var(--gray-500);">Progression : <?php echo $progression; ?>%</span>
                      <span style="font-size: 0.9rem; font-weight: 600; color: var(--accent-green);"><?php echo $valeurActuelle; ?>/<?php echo $objectifTotal; ?></span>
                    </div>
                    <div style="background-color: var(--gray-200); border-radius: var(--border-radius-sm); height: 8px; margin-bottom: 1rem; overflow: hidden;">
                      <div style="background-color: var(--accent-green); height: 100%; width: <?php echo $progression; ?>%; transition: width 0.3s;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                      <span style="font-size: 0.9rem; color: var(--gray-500);">
                        <?php 
                          $dateDebut = isset($defi['Date_Debut']) ? date('d M Y', strtotime($defi['Date_Debut'])) : 'Date non définie';
                          $dateFin = isset($defi['Date_Fin']) ? date('d M Y', strtotime($defi['Date_Fin'])) : 'Date non définie';
                          echo "Du $dateDebut au $dateFin";
                        ?>
                      </span>
                      <a href="#" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Participer</a>
                    </div>
                  </div>
                </div>
              <?php 
                  endif;
                endforeach; 
              ?>
            <?php endif; ?>
          </div>
        </div>
        <!-- Activité du quartier -->
        <div style="margin-bottom: 3rem;">
          <h2 style="margin-bottom: 1.5rem; color: var(--dark-green); font-size: 1.8rem;">Activité de mon quartier</h2>
          <div style="background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm);">
            <div style="padding: 1.5rem;">
              <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 1rem;">Événements à venir</h3>
              <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                  <thead>
                    <tr style="border-bottom: 1px solid var(--gray-300);">
                      <th style="width: 20%; padding: 0.75rem; text-align: left; color: var(--dark-green);">Date</th>
                      <th style="width: 50%; padding: 0.75rem; text-align: left; color: var(--dark-green);">Événement</th>
                      <th style="width: 20%; padding: 0.75rem; text-align: left; color: var(--dark-green);">Lieu</th>
                      <th style="width: 10%; padding: 0.75rem; text-align: center; color: var(--dark-green);">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr style="border-bottom: 1px solid var(--gray-200);">
                      <td style="padding: 0.75rem; color: var(--text-color);">12 juin 2023</td>
                      <td style="padding: 0.75rem; color: var(--text-color); font-weight: 500;">Journée de plantation d'arbres</td>
                      <td style="padding: 0.75rem; color: var(--text-color);">Parc Central</td>
                      <td style="padding: 0.75rem; text-align: center;"><a href="#" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Participer</a></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--gray-200);">
                      <td style="padding: 0.75rem; color: var(--text-color);">18 juin 2023</td>
                      <td style="padding: 0.75rem; color: var(--text-color); font-weight: 500;">Atelier compostage urbain</td>
                      <td style="padding: 0.75rem; color: var(--text-color);">Centre communautaire</td>
                      <td style="padding: 0.75rem; text-align: center;"><a href="#" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Participer</a></td>
                    </tr>
                    <tr>
                      <td style="padding: 0.75rem; color: var(--text-color);">25 juin 2023</td>
                      <td style="padding: 0.75rem; color: var(--text-color); font-weight: 500;">Nettoyage collectif des berges</td>
                      <td style="padding: 0.75rem; color: var(--text-color);">Rivière Est</td>
                      <td style="padding: 0.75rem; text-align: center;"><a href="#" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Participer</a></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <!-- SECTION HABITUDES DE TRANSPORT -->
        <div style="margin-bottom: 3rem;">
          <h2 style="margin-bottom: 1.5rem; color: var(--dark-green); font-size: 1.8rem;">Mes habitudes de transport</h2>
          <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
            <!-- Résumé des transports -->
            <div style="flex: 1; min-width: 300px; background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); padding: 1.5rem;">
              <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 1rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem;">
                <i class="fas fa-bicycle" style="margin-right: 0.5rem;"></i>Résumé de vos transports
              </h3>
              <?php
              $transports = [];
              $distanceHebdomadaire = 0;
              $nbTransports = 0;
              try {
                  $query = $db->prepare("SELECT * FROM TRANSPORT WHERE id_utilisateur = ?");
                  $query->execute([$userDbData->getId()]);
                  $transports = $query->fetchAll(PDO::FETCH_ASSOC);
                  $nbTransports = count($transports);
                  foreach ($transports as $transport) {
                      $distanceHebdomadaire += $transport['distance_parcourue'] * $transport['frequence'];
                  }
              } catch (PDOException $e) {
              }
              ?>
              <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div style="text-align: center;">
                  <div style="font-size: 2rem; font-weight: 700; color: var(--accent-green); margin-bottom: 0.25rem;"><?= $nbTransports ?></div>
                  <p style="color: var(--gray-500); margin: 0;">modes de transport enregistrés</p>
                </div>
                <div style="text-align: center;">
                  <div style="font-size: 2rem; font-weight: 700; color: var(--accent-green); margin-bottom: 0.25rem;"><?= number_format($distanceHebdomadaire, 1) ?> km</div>
                  <p style="color: var(--gray-500); margin: 0;">distance hebdomadaire estimée</p>
                </div>
                <?php if ($nbTransports > 0): ?>
                <div style="text-align: center;">
                  <div style="font-size: 2rem; font-weight: 700; color: var(--accent-green); margin-bottom: 0.25rem;"><?= number_format($transportStats['avg_eco_index'], 1) ?>/10</div>
                  <p style="color: var(--gray-500); margin: 0;">éco-index moyen</p>
                </div>
                <?php endif; ?>
              </div>
              <a href="/WaveNet/views/frontoffice/manageTransport.php" style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; background-color: var(--accent-green); color: var(--white); text-decoration: none; border-radius: var(--border-radius); font-weight: 500; transition: all var(--transition-speed);">
                <i class="fas fa-cog" style="margin-right: 0.5rem;"></i>Gérer mes transports
              </a>
            </div>
            <!-- Conseils éco-mobilité -->
            <div style="flex: 1; min-width: 300px; background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); padding: 1.5rem;">
              <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 1rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem;">
                <i class="fas fa-leaf" style="margin-right: 0.5rem;"></i>Conseils pour la mobilité verte
              </h3>
              <ul style="list-style: none; padding: 0; margin: 0;">
                <li style="padding: 0.75rem 0; border-left: 3px solid var(--accent-green); padding-left: 1rem; margin-bottom: 0.75rem; background-color: rgba(76, 175, 80, 0.05);">
                  <i class="fas fa-walking" style="color: var(--accent-green); margin-right: 0.5rem;"></i>
                  Privilégiez la marche pour les courts trajets
                </li>
                <li style="padding: 0.75rem 0; border-left: 3px solid var(--accent-green); padding-left: 1rem; margin-bottom: 0.75rem; background-color: rgba(76, 175, 80, 0.05);">
                  <i class="fas fa-bicycle" style="color: var(--accent-green); margin-right: 0.5rem;"></i>
                  Le vélo est idéal pour les distances moyennes
                </li>
                <li style="padding: 0.75rem 0; border-left: 3px solid var(--accent-green); padding-left: 1rem; margin-bottom: 0.75rem; background-color: rgba(76, 175, 80, 0.05);">
                  <i class="fas fa-bus-alt" style="color: var(--accent-green); margin-right: 0.5rem;"></i>
                  Utilisez les transports en commun pour réduire votre empreinte
                </li>
                <li style="padding: 0.75rem 0; border-left: 3px solid var(--accent-green); padding-left: 1rem; background-color: rgba(76, 175, 80, 0.05);">
                  <i class="fas fa-users" style="color: var(--accent-green); margin-right: 0.5rem;"></i>
                  Pensez au covoiturage pour partager les coûts
                </li>
              </ul>
            </div>
          </div>
        </div>
        <!-- SECTION SIGNALEMENTS -->
        <div style="margin-bottom: 3rem;">
          <h2 style="margin-bottom: 1.5rem; color: var(--dark-green); font-size: 1.8rem;">Signalements urbains</h2>
          <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
            <!-- Informations sur les signalements -->
            <div style="flex: 1; min-width: 300px; background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); padding: 1.5rem;">
              <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 1rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem;">
                <i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>Signaler un problème
              </h3>
              <p style="margin-bottom: 1.5rem;">
                Vous avez remarqué un problème dans votre quartier ? Un nid-de-poule, un lampadaire défectueux, 
                un dépôt sauvage ? Signalez-le pour contribuer à l'amélioration de votre cadre de vie.
              </p>
              <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="/WaveNet/views/frontoffice/addSignalement.php" style="flex: 1; min-width: 120px; display: inline-flex; align-items: center; justify-content: center; padding: 0.75rem 1rem; background-color: var(--accent-green); color: var(--white); text-decoration: none; border-radius: var(--border-radius); font-weight: 500; transition: all var(--transition-speed);">
                  <i class="fas fa-plus" style="margin-right: 0.5rem;"></i>Nouveau signalement
                </a>
                <a href="/WaveNet/views/frontoffice/viewSignalements.php" style="flex: 1; min-width: 120px; display: inline-flex; align-items: center; justify-content: center; padding: 0.75rem 1rem; background-color: var(--white); border: 1px solid var(--accent-green); color: var(--accent-green); text-decoration: none; border-radius: var(--border-radius); font-weight: 500; transition: all var(--transition-speed);">
                  <i class="fas fa-list" style="margin-right: 0.5rem;"></i>Voir mes signalements
                </a>
              </div>
              <div style="margin-top: 1rem;">
                <a href="/WaveNet/views/frontoffice/interventions.php" style="display: inline-flex; align-items: center; justify-content: center; padding: 0.5rem 1rem; background-color: var(--white); border: 1px solid #007bff; color: #007bff; text-decoration: none; border-radius: var(--border-radius); font-weight: 500; transition: all var(--transition-speed);">
                  <i class="fas fa-tools" style="margin-right: 0.5rem;"></i>Voir les interventions
                </a>
              </div>
            </div>
            <!-- Pourquoi signaler ? -->
            <div style="flex: 1; min-width: 300px; background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); padding: 1.5rem;">
              <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 1rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem;">
                <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>Pourquoi signaler ?
              </h3>
              <ul style="list-style: none; padding: 0; margin: 0;">
                <li style="padding: 0.75rem 0; border-left: 3px solid var(--accent-green); padding-left: 1rem; margin-bottom: 0.75rem; background-color: rgba(76, 175, 80, 0.05);">
                  <i class="fas fa-city" style="color: var(--accent-green); margin-right: 0.5rem;"></i>
                  Améliorez votre cadre de vie quotidien
                </li>
                <li style="padding: 0.75rem 0; border-left: 3px solid var(--accent-green); padding-left: 1rem; margin-bottom: 0.75rem; background-color: rgba(76, 175, 80, 0.05);">
                  <i class="fas fa-users" style="color: var(--accent-green); margin-right: 0.5rem;"></i>
                  Contribuez au bien-être collectif
                </li>
                <li style="padding: 0.75rem 0; border-left: 3px solid var(--accent-green); padding-left: 1rem; margin-bottom: 0.75rem; background-color: rgba(76, 175, 80, 0.05);">
                  <i class="fas fa-medal" style="color: var(--accent-green); margin-right: 0.5rem;"></i>
                  Gagnez des points verts pour votre engagement
                </li>
                <li style="padding: 0.75rem 0; border-left: 3px solid var(--accent-green); padding-left: 1rem; background-color: rgba(76, 175, 80, 0.05);">
                  <i class="fas fa-hand-holding-heart" style="color: var(--accent-green); margin-right: 0.5rem;"></i>
                  Participez à la transformation de votre quartier
                </li>
              </ul>
            </div>
          </div>
        </div>
        <!-- SECTION INFORMATIONS PERSONNELLES -->
        <div style="margin-bottom: 3rem;">
          <h2 style="margin-bottom: 1.5rem; color: var(--dark-green); font-size: 1.8rem;">Mon profil</h2>
          <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
            <!-- Informations utilisateur -->
            <div style="flex: 1; min-width: 300px; background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); padding: 1.5rem;">
              <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 1rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem;">
                <i class="fas fa-user-circle" style="margin-right: 0.5rem;"></i>Informations personnelles
              </h3>
              <div style="display: grid; grid-template-columns: auto 1fr; gap: 0.5rem 1rem; margin-bottom: 1.5rem;">
                <div style="font-weight: 600; color: var(--text-color);">Nom :</div>
                <div><?= htmlspecialchars($userDbData->getNom()) ?></div>
                <div style="font-weight: 600; color: var(--text-color);">Prénom :</div>
                <div><?= htmlspecialchars($userDbData->getPrenom()) ?></div>
                <div style="font-weight: 600; color: var(--text-color);">Email :</div>
                <div><?= htmlspecialchars($userDbData->getEmail()) ?></div>
                <div style="font-weight: 600; color: var(--text-color);">Quartier :</div>
                <div>
                  <?php
                  echo $quartierName;
                  ?>
                </div>
              </div>
              <a href="/WaveNet/views/frontoffice/editProfile.php" style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; background-color: var(--accent-green); color: var(--white); text-decoration: none; border-radius: var(--border-radius); font-weight: 500; transition: all var(--transition-speed);">
                <i class="fas fa-edit" style="margin-right: 0.5rem;"></i>Modifier mon profil
              </a>
            </div>
            <!-- Progression et badges -->
            <div style="flex: 1; min-width: 300px; background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); padding: 1.5rem;">
              <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 1rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem;">
                <i class="fas fa-trophy" style="margin-right: 0.5rem;"></i>Progression
              </h3>
              <?php
              $points = $userDbData->getPointsVerts();
              $nextLevel = 100;
              $levelLabel = "Intermédiaire";
              if ($points > 1000) {
                  $progressPercent = 100;
                  $levelLabel = "Expert";
              } else if ($points > 500) {
                  $progressPercent = ($points - 500) / 5;
                  $nextLevel = 1000;
                  $levelLabel = "Expert";
              } else if ($points > 100) {
                  $progressPercent = ($points - 100) / 4;
                  $nextLevel = 500;
                  $levelLabel = "Avancé";
              } else {
                  $progressPercent = $points;
                  $levelLabel = "Intermédiaire";
              }
              ?>
              <div style="margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                  <span style="font-size: 0.9rem; color: var(--text-color);">Progression vers niveau <?= $levelLabel ?></span>
                  <span style="font-size: 0.9rem; font-weight: 600; color: var(--accent-green);"><?= $points ?>/<?= $nextLevel ?></span>
                </div>
                <div style="height: 8px; background-color: var(--gray-200); border-radius: 4px; overflow: hidden;">
                  <div style="height: 100%; width: <?= $progressPercent ?>%; background-color: var(--accent-green); transition: width 0.3s;"></div>
                </div>
              </div>
              <h4 style="font-size: 1rem; color: var(--dark-green); margin-bottom: 1rem;">Badges obtenus</h4>
              <div style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center;">
                <div style="text-align: center; width: 70px; opacity: <?= $points >= 10 ? '1' : '0.4' ?>;">
                  <i class="fas fa-seedling" style="font-size: 1.5rem; color: var(--accent-green); margin-bottom: 0.25rem;"></i>
                  <div style="font-size: 0.8rem;">Débutant</div>
                </div>
                <div style="text-align: center; width: 70px; opacity: <?= $points >= 100 ? '1' : '0.4' ?>;">
                  <i class="fas fa-leaf" style="font-size: 1.5rem; color: var(--accent-green); margin-bottom: 0.25rem;"></i>
                  <div style="font-size: 0.8rem;">Écologiste</div>
                </div>
                <div style="text-align: center; width: 70px; opacity: <?= $points >= 500 ? '1' : '0.4' ?>;">
                  <i class="fas fa-tree" style="font-size: 1.5rem; color: var(--accent-green); margin-bottom: 0.25rem;"></i>
                  <div style="font-size: 0.8rem;">Champion</div>
                </div>
                <div style="text-align: center; width: 70px; opacity: <?= $points >= 1000 ? '1' : '0.4' ?>;">
                  <i class="fas fa-globe-americas" style="font-size: 1.5rem; color: var(--accent-green); margin-bottom: 0.25rem;"></i>
                  <div style="font-size: 0.8rem;">Éco-héros</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Lien vers la gestion de l'authentification à deux facteurs -->
        <div class="card mb-3">
            <div class="card-header">
                <h5><i class="fas fa-shield-alt"></i> Sécurité du compte</h5>
            </div>
            <div class="card-body">
                <p>Renforcez la sécurité de votre compte en activant l'authentification à deux facteurs (2FA).</p>
                <a href="/WaveNet/controller/UserController.php?action=gerer2FA" class="btn btn-primary">
                    <i class="fas fa-lock"></i> Gérer l'authentification à deux facteurs
                </a>
            </div>
        </div>
        <!-- Suggestions de sécurité -->
        <?php
        $securitySuggestions = getSecuritySuggestions($userId);
        if (!empty($securitySuggestions)):
        ?>
        <div style="margin-bottom: 3rem;">
          <h2 style="margin-bottom: 1.5rem; color: var(--dark-green); font-size: 1.8rem;">Suggestions de sécurité</h2>
          <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($securitySuggestions as $suggestion): ?>
              <div style="background-color: <?php 
                  if ($suggestion['type'] === 'danger') echo '#FFF5F5'; 
                  elseif ($suggestion['type'] === 'warning') echo '#FFFBEB'; 
                  elseif ($suggestion['type'] === 'info') echo '#EBF8FF'; 
                  else echo '#F0FFF4'; 
                ?>; 
                border-left: 4px solid <?php 
                  if ($suggestion['type'] === 'danger') echo '#F56565'; 
                  elseif ($suggestion['type'] === 'warning') echo '#ED8936'; 
                  elseif ($suggestion['type'] === 'info') echo '#4299E1'; 
                  else echo '#48BB78'; 
                ?>; 
                padding: 1.5rem; border-radius: var(--border-radius);">
                <div style="display: flex; align-items: flex-start; gap: 1rem;">
                  <div style="font-size: 1.5rem; color: <?php 
                    if ($suggestion['type'] === 'danger') echo '#F56565'; 
                    elseif ($suggestion['type'] === 'warning') echo '#ED8936'; 
                    elseif ($suggestion['type'] === 'info') echo '#4299E1'; 
                    else echo '#48BB78'; 
                  ?>;">
                    <i class="fas <?php echo $suggestion['icon']; ?>"></i>
                  </div>
                  <div style="flex: 1;">
                    <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 0.5rem;"><?php echo $suggestion['title']; ?></h3>
                    <p style="color: var(--text-color); margin-bottom: 1rem;"><?php echo $suggestion['message']; ?></p>
                    <?php if (isset($suggestion['action']) && isset($suggestion['action_text'])): ?>
                      <a href="<?php echo $suggestion['action']; ?>" class="btn btn-sm" style="display: inline-block; padding: 0.4rem 1rem; font-size: 0.85rem; background-color: <?php 
                        if ($suggestion['type'] === 'danger') echo '#F56565'; 
                        elseif ($suggestion['type'] === 'warning') echo '#ED8936'; 
                        elseif ($suggestion['type'] === 'info') echo '#4299E1'; 
                        else echo '#48BB78'; 
                      ?>; color: white; border-radius: var(--border-radius-sm); text-decoration: none;">
                        <?php echo $suggestion['action_text']; ?>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- SECTION QUARTIERS ET INFRASTRUCTURES -->
        <div style="margin-bottom: 3rem;">
          <h2 style="margin-bottom: 1.5rem; color: var(--dark-green); font-size: 1.8rem;">Découverte urbaine</h2>
          <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
            <!-- Quartiers -->
            <div style="flex: 1; min-width: 300px; background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); padding: 1.5rem;">
              <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 1rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem;">
                <i class="fas fa-map-marked-alt" style="margin-right: 0.5rem;"></i>Quartiers
              </h3>
              <p style="color: var(--gray-700); margin-bottom: 1.5rem;">Explorez les quartiers de la ville, consultez leurs scores écologiques et découvrez les infrastructures disponibles.</p>
              <div style="display: flex; gap: 1rem;">
                <a href="/WaveNet/views/frontoffice/frontquartier.php" class="btn" style="background-color: var(--accent-green); color: var(--white); padding: 0.5rem 1rem; border-radius: var(--border-radius); text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-weight: 500; transition: all 0.3s;">
                  <i class="fas fa-search" style="margin-right: 0.5rem;"></i>Découvrir les quartiers
                </a>
              </div>
            </div>
            
            <!-- Infrastructures -->
            <div style="flex: 1; min-width: 300px; background-color: var(--white); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); padding: 1.5rem;">
              <h3 style="color: var(--dark-green); font-size: 1.2rem; margin-bottom: 1rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem;">
                <i class="fas fa-building" style="margin-right: 0.5rem;"></i>Infrastructures
              </h3>
              <p style="color: var(--gray-700); margin-bottom: 1.5rem;">Consultez les infrastructures urbaines disponibles dans chaque quartier de la ville, leur statut et leurs détails.</p>
              <div style="display: flex; gap: 1rem;">
                <a href="/WaveNet/views/frontoffice/frontinfra.php" class="btn" style="background-color: var(--accent-green); color: var(--white); padding: 0.5rem 1rem; border-radius: var(--border-radius); text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-weight: 500; transition: all 0.3s;">
                  <i class="fas fa-list" style="margin-right: 0.5rem;"></i>Voir les infrastructures
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- SECTION PARTICIPATION CITOYENNE -->
      </div>
    </div>
  </div>
  <?php
  $additionalScripts = <<<EOT
  <script>
    function showError(message, targetDivId = 'profile-message') {
      const messageDiv = document.getElementById(targetDivId);
      if (messageDiv) {
        messageDiv.innerHTML = `<div style="padding: 0.75rem; background-color: #ffebee; color: #c62828; border-radius: var(--border-radius); margin-bottom: 1rem;">\${message}</div>`;
        messageDiv.style.display = 'block';
        const rect = messageDiv.getBoundingClientRect();
        if (rect.top < 0 || rect.bottom > window.innerHeight) {
            window.scrollTo({ top: messageDiv.offsetTop - 100, behavior: 'smooth' });
        }
      }
    }
    function showSuccess(message, targetDivId = 'profile-message') {
      const messageDiv = document.getElementById(targetDivId);
      if (messageDiv) {
        messageDiv.innerHTML = `<div style="padding: 0.75rem; background-color: #e8f5e9; color: #2e7d32; border-radius: var(--border-radius); margin-bottom: 1rem;">\${message}</div>`;
        messageDiv.style.display = 'block';
        const rect = messageDiv.getBoundingClientRect();
        if (rect.top < 0 || rect.bottom > window.innerHeight) {
            window.scrollTo({ top: messageDiv.offsetTop - 100, behavior: 'smooth' });
        }
      }
    }
    document.addEventListener('DOMContentLoaded', function() {
        const profileMessageDiv = document.getElementById('profile-message');
        if (profileMessageDiv && profileMessageDiv.innerHTML.trim() !== '') {
            profileMessageDiv.style.display = 'block';
        }
    });
  </script>
  EOT;
  require_once '../includes/footer.php';
  ?>
</body>
</html>
<style>
.badge-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px;
    border-radius: 8px;
    width: 100px;
    text-align: center;
}
.badge-item.active {
    background-color: rgba(40, 167, 69, 0.2);
    color: #28a745;
}
.badge-item.inactive {
    background-color: #f8f9fa;
    color: #adb5bd;
}
.badge-item span {
    margin-top: 5px;
    font-size: 0.8rem;
}
.eco-tips .list-group-item {
    border-left: 4px solid #28a745;
}
.transport-summary, .user-info-summary, .eco-tips, .progress-section {
    padding: 20px;
    height: 100%;
    border-radius: 8px;
    background-color: #f8f9fa;
}
/* Styles pour les suggestions de sécurité */
.security-suggestions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.security-card {
  display: flex;
  align-items: flex-start;
  padding: 1.25rem;
  border-radius: var(--border-radius);
  background-color: var(--white);
  border-left: 4px solid;
  box-shadow: var(--shadow-sm);
}

.security-warning {
  border-left-color: #f0ad4e;
}

.security-danger {
  border-left-color: #d9534f;
}

.security-info {
  border-left-color: #5bc0de;
}

.security-icon {
  font-size: 1.5rem;
  margin-right: 1rem;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.security-warning .security-icon {
  color: #f0ad4e;
  background-color: rgba(240, 173, 78, 0.1);
}

.security-danger .security-icon {
  color: #d9534f;
  background-color: rgba(217, 83, 79, 0.1);
}

.security-info .security-icon {
  color: #5bc0de;
  background-color: rgba(91, 192, 222, 0.1);
}

.security-content {
  flex: 1;
}

.security-content h3 {
  font-size: 1.1rem;
  margin-top: 0;
  margin-bottom: 0.5rem;
  color: var(--dark-green);
}

.security-content p {
  margin-bottom: 1rem;
  color: var(--text-color);
}

.security-content .btn {
  padding: 0.4rem 0.8rem;
  font-size: 0.9rem;
}
</style>
<script>
$(function() {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
