<?php
// Utiliser les bons chemins d'inclusion
include_once $_SERVER['DOCUMENT_ROOT'] . '/WaveNet/views/includes/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/WaveNet/models/intervention.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/WaveNet/models/signalement.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/WaveNet/controller/interventionctrl.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/WaveNet/controller/signalementctrl.php';

// Vérifier que la classe Config existe déjà, sinon la créer
if (!class_exists('Config')) {
    class Config {
        public static function getConnection() {
            return connectDB(); // Utilise la fonction du fichier config.php principal
        }
    }
}

// Initialiser les contrôleurs
$interventionController = new InterventionC();
$signalementController = new SignalementC();

// Récupérer toutes les interventions
$interventions = $interventionController->afficherIntervention();

// Vérifie si l'utilisateur est connecté
if (session_status() === PHP_SESSION_NONE) { 
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page.";
    header('Location: /WaveNet/views/frontoffice/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Interventions | WaveNet</title>
  <link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css">
  <style>
    /* Styles spécifiques pour cette page */
    .intervention-card {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.5s ease, transform 0.5s ease;
      margin-bottom: 20px;
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }
    
    .interventions-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
      padding: 20px;
      max-width: 1200px;
      margin: 0 auto;
    }
    
    .card-header {
      padding: 15px;
      color: white;
      position: relative;
    }
    
    .type-problem .card-header {
      background-color: #e53935; /* Rouge */
    }
    
    .type-warning .card-header {
      background-color: #fb8c00; /* Orange */
    }
    
    .type-solution .card-header {
      background-color: #43a047; /* Vert */
    }
    
    .card-content {
      padding: 15px;
    }
    
    .card-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 15px;
      background-color: #f5f5f5;
      border-top: 1px solid #eee;
    }
    
    .stats {
      display: flex;
      flex-direction: column;
      gap: 10px;
      font-size: 13px;
      color: #666;
      margin: 15px 0;
    }
    
    .statut {
      display: inline-block;
      padding: 5px 10px;
      border-radius: 5px;
      font-weight: bold;
    }
    
    .statut.non-traité {
      background-color: rgba(229, 57, 53, 0.2);
      color: #e53935;
    }
    
    .statut.en-cours {
      background-color: rgba(251, 140, 0, 0.2);
      color: #fb8c00;
    }
    
    .statut.traité {
      background-color: rgba(67, 160, 71, 0.2);
      color: #43a047;
    }
    
    .button {
      display: inline-block;
      padding: 8px 15px;
      background-color: var(--accent-green);
      color: white;
      text-decoration: none;
      border-radius: 4px;
      font-size: 14px;
      transition: background-color 0.3s;
    }
    
    .button:hover {
      background-color: var(--dark-green);
    }
    
    .signalement-info {
      margin-top: 15px;
      padding-top: 15px;
      border-top: 1px solid #eee;
    }
  </style>
</head>
<body>
  <!-- En-tête et navigation seront inclus ici -->
  <?php require_once '../includes/userHeader.php'; ?>
  
  <section class="interventions-section">
    <div class="container">
      <div class="interventions-header">
        <h2>Liste des Interventions</h2>
        <p>Consultez toutes les interventions liées aux signalements</p>
      </div>
      
      <div class="interventions-grid">
        <?php
        
        if (count($interventions) > 0) {
          
          foreach ($interventions as $intervention) {
            
            $cardClass = '';
            if ($intervention['statut'] === 'non traité') {
              $cardClass = 'type-problem'; 
            } elseif ($intervention['statut'] === 'en cours') {
              $cardClass = 'type-warning'; 
            } elseif ($intervention['statut'] === 'traité') {
              $cardClass = 'type-solution'; 
            }
            
            
            // Utiliser la méthode rechercher du SignalementC
            $signalementData = $signalementController->rechercher($intervention['id_signalement']);
            $signalement = !empty($signalementData) ? $signalementData[0] : null;
            ?>
            <div class="intervention-card <?php echo $cardClass; ?>">
              <div class="card-header">
                <h3>Intervention #<?php echo $intervention['id_intervention']; ?></h3>
                <span class="category"><?php echo $intervention['type_intervention'] ?? ''; ?></span>
              </div>
              <div class="card-content">
                <h4>Description</h4>
                <p><?php echo $intervention['description'] ?? 'Pas de description disponible'; ?></p>
                
                <div class="signalement-info">
                  <h5>Signalement associé</h5>
                  <?php if ($signalement): ?>
                    <p><strong>ID:</strong> #<?php echo $signalement['id_signalement']; ?></p>
                    <p><strong>Description:</strong> <?php echo $signalement['description']; ?></p>
                    <p><strong>Localisation:</strong> <?php echo $signalement['emplacement']; ?></p>
                    <a href="/WaveNet/views/frontoffice/viewSignalements.php" class="signalement-link">
                      Voir mes signalements
                    </a>
                  <?php else: ?>
                    <p>Aucun signalement associé</p>
                  <?php endif; ?>
                </div>
                
                <div class="stats">
                  <div class="stat-item">
                    <span class="statut <?php echo str_replace(' ', '-', strtolower($intervention['statut'])); ?>">
                      <?php echo $intervention['statut']; ?>
                    </span>
                  </div>
                  <div class="stat-item">
                    <p><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($intervention['date_intervention'])); ?></p>
                  </div>
                  <?php if (isset($intervention['duree'])): ?>
                  <div class="stat-item">
                    <p><strong>Durée:</strong> <?php echo $intervention['duree']; ?> heures</p>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="card-footer">
                <a href="/WaveNet/views/frontoffice/viewSignalements.php" class="button">
                  Voir mes signalements
                </a>
              </div>
            </div>
            <?php
          }
        } else {
          
          ?>
          <div class="no-interventions">
            <p>Aucune intervention n'est actuellement disponible.</p>
            <p>Vous pouvez créer un signalement pour qu'une intervention soit planifiée.</p>
            <a href="/WaveNet/views/frontoffice/addSignalement.php" class="button">Créer un signalement</a>
          </div>
          <?php
        }
        ?>
      </div>
    </div>
  </section>
  
  <?php require_once '../includes/footer.php'; ?>

  <script>
    
    document.addEventListener('DOMContentLoaded', function() {
      const cards = document.querySelectorAll('.intervention-card');
      cards.forEach((card, index) => {
        setTimeout(() => {
          card.style.opacity = '1';
          card.style.transform = 'translateY(0)';
        }, 100 * index);
      });
    });
  </script>
</body>
</html> 