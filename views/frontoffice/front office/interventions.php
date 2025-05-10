<?php
// Utiliser le fichier config.php existant dans views/includes
include_once $_SERVER['DOCUMENT_ROOT'] . '/WaveNet/views/includes/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/WaveNet/gestion signalement/model/intervention.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/WaveNet/gestion signalement/model/signalement.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/WaveNet/gestion signalement/controller/interventionctrl.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/WaveNet/gestion signalement/controller/signalementctrl.php';

// Créer une classe Config compatible dans cet environnement
class Config {
    public static function getConnection() {
        return connectDB(); // Utilise la fonction du fichier config.php principal
    }
}

// Initialiser les contrôleurs à la place des DAO
$interventionController = new InterventionC();
$signalementController = new SignalementC();

// Récupérer toutes les interventions
$interventions = $interventionController->afficherIntervention();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Interventions | Gestion Signalement</title>
  <link rel="stylesheet" href="css/style11.css">
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
  <header class="main-header">
    <nav class="nav-container">
        <div class="logo">
            <h1>Urbaverse</h1>
        </div>
        <ul class="nav-links">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="../back office/dashboard.php">Dashboard</a></li>
            <li><a href="blog.html">Blog</a></li>
            <li><a href="index.php">Signaler</a></li>
            <li><a href="interventions.php">Interventions</a></li>
            <li><a href="about.html">À propos</a></li>
        </ul>
        <div class="user-actions">
            <span class="points">Points verts: 150</span>
            <a href="#login" class="btn btn-secondary">Connexion</a>
        </div>
    </nav>
  </header>
  
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
                <span class="category"><?php echo $intervention['type_intervention']; ?></span>
              </div>
              <div class="card-content">
                <h4>Description</h4>
                <p><?php echo $intervention['description']; ?></p>
                
                <div class="signalement-info">
                  <h5>Signalement associé</h5>
                  <?php if ($signalement): ?>
                    <p><strong>ID:</strong> #<?php echo $signalement['id_signalement']; ?></p>
                    <p><strong>Description:</strong> <?php echo $signalement['description']; ?></p>
                    <p><strong>Localisation:</strong> <?php echo $signalement['emplacement']; ?></p>
                    <a href="../back office/modifiersignalement.php?id=<?php echo $signalement['id_signalement']; ?>" class="signalement-link">
                      Voir le signalement complet
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
                  <div class="stat-item">
                    <p><strong>Durée:</strong> <?php echo $intervention['duree']; ?> heures</p>
                  </div>
                </div>
              </div>
              <div class="card-footer">
                <a href="../back office/afficherintervention.php" class="button">
                  Voir détails
                </a>
                <?php if ($intervention['statut'] !== 'traité'): ?>
                  <a href="../back office/modifierintervention.php?id=<?php echo $intervention['id_intervention']; ?>" class="button">
                    Mettre à jour
                  </a>
                <?php endif; ?>
              </div>
            </div>
            <?php
          }
        } else {
          
          ?>
          <div class="no-interventions">
            <p>Aucune intervention n'est actuellement disponible.</p>
          </div>
          <?php
        }
        ?>
      </div>
    </div>
  </section>
  
  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <h4>À propos</h4>
        <p>Gestion Signalement est une plateforme dédiée à l'amélioration de notre environnement urbain.</p>
      </div>
      <div class="footer-section">
        <h4>Liens rapides</h4>
        <p><a href="index.php">Accueil</a></p>
        <p><a href="signalements.php">Signalements</a></p>
        <p><a href="interventions.php">Interventions</a></p>
      </div>
      <div class="footer-section">
        <h4>Contact</h4>
        <p>Email: contact@gestionsignalement.com</p>
        <p>Téléphone: +33 1 23 45 67 89</p>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2023 Gestion Signalement. Tous droits réservés.</p>
    </div>
  </footer>

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