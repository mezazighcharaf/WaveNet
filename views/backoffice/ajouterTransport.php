<?php
session_start();
// Vérifier l'authentification
if (!isset($_SESSION['user_id']) || $_SESSION['user_niveau'] !== 'admin') {
    header('Location: /WaveNet/views/frontoffice/login.php');
    exit;
}

require_once '../../views/includes/config.php';
$db = connectDB();
$pageTitle = "Ajouter un Type de Transport";
$alertMessage = '';

// Traitement du formulaire d'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $ecoIndex = floatval($_POST['eco_index']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    
    // Validation
    if (empty($nom)) {
        $alertMessage = "<div class='alert alert-danger'>Le nom du transport est requis.</div>";
    } elseif ($ecoIndex < 0 || $ecoIndex > 10) {
        $alertMessage = "<div class='alert alert-danger'>L'éco-index doit être entre 0 et 10.</div>";
    } else {
        try {
            // Vérifier si le type existe déjà
            $stmt = $db->prepare("SELECT COUNT(*) FROM TRANSPORT_TYPE WHERE nom = ?");
            $stmt->execute([$nom]);
            $exists = $stmt->fetchColumn() > 0;
            
            if ($exists) {
                $alertMessage = "<div class='alert alert-danger'>Ce nom de type de transport est déjà utilisé.</div>";
            } else {
                // Ajouter le nouveau type
                $stmt = $db->prepare("INSERT INTO TRANSPORT_TYPE (nom, eco_index, description) VALUES (?, ?, ?)");
                if ($stmt->execute([$nom, $ecoIndex, $description])) {
                    $_SESSION['alertMessage'] = "<div class='alert alert-success'>Type de transport ajouté avec succès.</div>";
                    header('Location: /WaveNet/views/backoffice/gererTransport.php');
                    exit;
                } else {
                    $alertMessage = "<div class='alert alert-danger'>Erreur lors de l'ajout du type de transport.</div>";
                }
            }
        } catch (PDOException $e) {
            $alertMessage = "<div class='alert alert-danger'>Erreur: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $pageTitle ?> - WaveNet</title>
  <link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css" />
  <link rel="stylesheet" href="/WaveNet/views/assets/css/admin-dashboard.css" />
  <link rel="stylesheet" href="/WaveNet/views/assets/css/backoffice11.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        border: none;
    }
    
    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.25rem 1.5rem;
    }
    
    .card-body {
        padding: 2rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #2e4f3e;
    }
    
    .form-control {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #4caf50;
        box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
    }
    
    .btn {
        padding: 0.6rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background-color: #4caf50;
        border-color: #4caf50;
    }
    
    .btn-primary:hover {
        background-color: #3d8b40;
        border-color: #3d8b40;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .btn-secondary {
        background-color: #f2f2f2;
        border-color: #e0e0e0;
        color: #555;
    }
    
    .btn-secondary:hover {
        background-color: #e0e0e0;
        color: #333;
    }
    
    .alert {
        border-radius: 8px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid;
    }
    
    .alert-success {
        background-color: rgba(76, 175, 80, 0.1);
        border-color: #4caf50;
        color: #2e7d32;
    }
    
    .alert-danger {
        background-color: rgba(244, 67, 54, 0.1);
        border-color: #f44336;
        color: #d32f2f;
    }
    
    .form-text {
        font-size: 0.85rem;
        margin-top: 0.5rem;
    }
    
    .icon-options {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 0.5rem;
        margin-top: 1rem;
    }
    
    .icon-option {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0.75rem;
        border: 1px solid #eee;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .icon-option:hover {
        background-color: #f8f9fc;
        border-color: #4caf50;
    }
    
    .icon-option i {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: #4caf50;
    }
    
    .icon-option.selected {
        background-color: rgba(76, 175, 80, 0.1);
        border-color: #4caf50;
    }
  </style>
</head>
<body>
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="logo">
      <img src="/WaveNet/views/assets/images/logo.png" alt="Logo" class="logo-img">
      <h1>WaveNet</h1>
    </div>
    <nav class="sidebar-nav">
      <ul>
        <li><a href="/WaveNet/views/backoffice/index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="/WaveNet/views/backoffice/listeUtilisateurs.php"><i class="fas fa-users"></i> Utilisateurs</a></li>
        <li><a href="/WaveNet/views/backoffice/defis.php"><i class="fas fa-trophy"></i> Défis</a></li>
        <li><a href="/WaveNet/views/backoffice/Gquartier.php"><i class="fas fa-map-marker-alt"></i> Quartiers</a></li>
        <li><a href="/WaveNet/views/backoffice/backinfra.php"><i class="fas fa-building"></i> Infrastructures</a></li>
        <li><a href="/WaveNet/views/backoffice/gsignalement.php"><i class="fas fa-exclamation-triangle"></i> Signalements</a></li>
        <li><a href="/WaveNet/views/backoffice/interventions.php"><i class="fas fa-tools"></i> Interventions</a></li>
        <li><a href="/WaveNet/views/backoffice/recompenseback.php"><i class="fas fa-gift"></i> Récompenses</a></li>
        <li><a href="/WaveNet/views/backoffice/eco_actionsB.php"><i class="fas fa-leaf"></i> Eco Actions</a></li>
        <li><a href="/WaveNet/views/backoffice/gererTransport.php" class="active"><i class="fas fa-car"></i> Types de Transport</a></li>
        <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php"><i class="fas fa-home"></i> Accueil frontoffice</a></li>
      </ul>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <header class="content-header">
      <h1><?= $pageTitle ?></h1>
      <div class="user-info">
        <span><?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?> (Admin)</span>
        <a href="/WaveNet/controller/UserController.php?action=logout" class="home-button">Déconnexion</a>
      </div>
    </header>
    
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Ajouter un Type de Transport</h6>
                <a href="/WaveNet/views/backoffice/gererTransport.php" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
            <div class="card-body">
                <?= $alertMessage ?>
                
                <form method="post" action="/WaveNet/views/backoffice/ajouterTransport.php" class="transport-form">
                    <div class="form-group">
                        <label for="nom">Nom du Type de Transport</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>" required>
                        <small class="form-text text-muted">Exemple: Vélo, Bus, Train, etc.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="eco_index">Éco-index (0-10)</label>
                        <input type="number" class="form-control" id="eco_index" name="eco_index" min="0" max="10" step="0.1" value="<?= isset($_POST['eco_index']) ? htmlspecialchars($_POST['eco_index']) : '5.0' ?>" required>
                        <small class="form-text text-muted">0 = Impact très négatif, 10 = Impact très positif</small>
                        
                        <div class="mt-3">
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 33%"></div>
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 33%"></div>
                                <div class="progress-bar bg-success" role="progressbar" style="width: 34%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small>0 (Mauvais)</small>
                                <small>5 (Moyen)</small>
                                <small>10 (Excellent)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                        <small class="form-text text-muted">Décrivez brièvement ce type de transport et son impact environnemental.</small>
                    </div>
                    
                    <div class="form-group d-flex justify-content-between mt-4">
                        <a href="/WaveNet/views/backoffice/gererTransport.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
  </main>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function() {
        // Mise à jour dynamique de l'indicateur d'éco-index
        $('#eco_index').on('input', function() {
            let val = parseFloat($(this).val());
            let progressWidth = (val / 10) * 100;
            let color;
            
            if (val <= 3) {
                color = 'danger';
            } else if (val <= 6) {
                color = 'warning';
            } else {
                color = 'success';
            }
            
            $('.progress-bar').removeClass('bg-danger bg-warning bg-success').css('width', '0%');
            $('.progress-bar.' + 'bg-' + color).css('width', progressWidth + '%');
        });
        
        // Validation du formulaire
        $('.transport-form').on('submit', function(e) {
            if (!$('#nom').val().trim()) {
                e.preventDefault();
                alert('Le nom du transport est requis.');
                return false;
            }
            return true;
        });
    });
  </script>
</body>
</html> 