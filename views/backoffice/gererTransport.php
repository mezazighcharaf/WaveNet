<?php
session_start();
// Ajouter des logs pour déboguer la session
error_log("[gererTransport.php] Session: " . print_r($_SESSION, true));

// CORRECTION PRINCIPALE: Modifier la vérification pour utiliser 'user_niveau' au lieu de 'niveau'
if (!isset($_SESSION['user_id']) || $_SESSION['user_niveau'] !== 'admin') {
    error_log("[gererTransport.php] Redirection vers login.php. user_id: " . 
        (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'non défini') . 
        ", user_niveau: " . (isset($_SESSION['user_niveau']) ? $_SESSION['user_niveau'] : 'non défini'));
    header('Location: /WaveNet/views/frontoffice/login.php');
    exit;
}

require_once '../../views/includes/config.php';
$db = connectDB();
$pageTitle = "Gestion des Types de Transport";
$alertMessage = '';

// Récupérer les messages de la session
if (isset($_SESSION['alertMessage'])) {
    $alertMessage = $_SESSION['alertMessage'];
    unset($_SESSION['alertMessage']);
}

// Traitement du formulaire d'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $nom = trim($_POST['nom']);
        $ecoIndex = floatval($_POST['eco_index']);
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        
        // Validation
        if (empty($nom)) {
            $alertMessage = "<div class='alert alert-danger'>Le nom du transport est requis.</div>";
        } elseif ($ecoIndex < 0 || $ecoIndex > 10) {
            $alertMessage = "<div class='alert alert-danger'>L'éco-index doit être entre 0 et 10.</div>";
        } else {
            // Vérifier si le type existe déjà
            $stmt = $db->prepare("SELECT COUNT(*) FROM TRANSPORT_TYPE WHERE nom = ?");
            $stmt->execute([$nom]);
            $exists = $stmt->fetchColumn() > 0;
            
            if ($exists) {
                $alertMessage = "<div class='alert alert-danger'>Ce type de transport existe déjà.</div>";
            } else {
                // Ajouter le nouveau type
                $stmt = $db->prepare("INSERT INTO TRANSPORT_TYPE (nom, eco_index, description) VALUES (?, ?, ?)");
                if ($stmt->execute([$nom, $ecoIndex, $description])) {
                    $alertMessage = "<div class='alert alert-success'>Type de transport ajouté avec succès.</div>";
                } else {
                    $alertMessage = "<div class='alert alert-danger'>Erreur lors de l'ajout du type de transport.</div>";
                }
            }
        }
    } elseif ($_POST['action'] === 'edit') {
        $id = intval($_POST['id']);
        $nom = trim($_POST['nom']);
        $ecoIndex = floatval($_POST['eco_index']);
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        
        // Validation
        if (empty($nom)) {
            $alertMessage = "<div class='alert alert-danger'>Le nom du transport est requis.</div>";
        } elseif ($ecoIndex < 0 || $ecoIndex > 10) {
            $alertMessage = "<div class='alert alert-danger'>L'éco-index doit être entre 0 et 10.</div>";
        } else {
            // Mettre à jour le type
            $stmt = $db->prepare("UPDATE TRANSPORT_TYPE SET nom = ?, eco_index = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$nom, $ecoIndex, $description, $id])) {
                $alertMessage = "<div class='alert alert-success'>Type de transport mis à jour avec succès.</div>";
            } else {
                $alertMessage = "<div class='alert alert-danger'>Erreur lors de la mise à jour du type de transport.</div>";
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $id = intval($_POST['id']);
        
        // Vérifier si le type est utilisé
        $stmt = $db->prepare("SELECT COUNT(*) FROM TRANSPORT WHERE type_transport IN (SELECT nom FROM TRANSPORT_TYPE WHERE id = ?)");
        $stmt->execute([$id]);
        $isUsed = $stmt->fetchColumn() > 0;
        
        if ($isUsed) {
            $alertMessage = "<div class='alert alert-danger'>Ce type de transport est utilisé et ne peut pas être supprimé.</div>";
        } else {
            // Supprimer le type
            $stmt = $db->prepare("DELETE FROM TRANSPORT_TYPE WHERE id = ?");
            if ($stmt->execute([$id])) {
                $alertMessage = "<div class='alert alert-success'>Type de transport supprimé avec succès.</div>";
            } else {
                $alertMessage = "<div class='alert alert-danger'>Erreur lors de la suppression du type de transport.</div>";
            }
        }
    }
}

// Récupérer tous les types de transport
$transportTypes = [];
try {
    $tableExists = $db->query("SHOW TABLES LIKE 'TRANSPORT_TYPE'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Create the TRANSPORT_TYPE table with proper ID column as AUTO_INCREMENT
        $db->exec("CREATE TABLE TRANSPORT_TYPE (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL UNIQUE,
            eco_index FLOAT NOT NULL,
            description TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $defaultTypes = [
            ['Marche', 10.0, 'Déplacement à pied, aucune émission de CO2'],
            ['Vélo', 9.5, 'Déplacement à vélo, très faible impact environnemental'],
            ['Transport en commun (Bus)', 7.0, 'Déplacement en bus, impact modéré'],
            ['Transport en commun (Tram/Métro)', 7.5, 'Déplacement en tram ou métro, impact modéré'],
            ['Voiture électrique', 6.0, 'Déplacement en voiture électrique, impact modéré à faible'],
            ['Covoiturage', 5.0, 'Déplacement en covoiturage, impact modéré'],
            ['Voiture thermique', 2.0, 'Déplacement en voiture individuelle, impact élevé'],
            ['Trottinette électrique', 8.0, 'Déplacement en trottinette électrique, impact faible']
        ];
        
        $stmt = $db->prepare("INSERT INTO TRANSPORT_TYPE (nom, eco_index, description) VALUES (?, ?, ?)");
        foreach ($defaultTypes as $type) {
            $stmt->execute($type);
        }
        
        $alertMessage = "<div class='alert alert-info'>Table des types de transport initialisée avec des valeurs par défaut.</div>";
    } else {
        // Check if the table has the correct structure (id column may be missing)
        $hasIdColumn = false;
        $columns = $db->query("SHOW COLUMNS FROM TRANSPORT_TYPE")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            if ($column['Field'] === 'id') {
                $hasIdColumn = true;
                break;
            }
        }
        
        // If id column doesn't exist, recreate the table with proper structure
        if (!$hasIdColumn) {
            // First, backup existing data
            $oldData = $db->query("SELECT * FROM TRANSPORT_TYPE")->fetchAll(PDO::FETCH_ASSOC);
            
            // Drop and recreate the table
            $db->exec("DROP TABLE IF EXISTS TRANSPORT_TYPE");
            $db->exec("CREATE TABLE TRANSPORT_TYPE (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(100) NOT NULL UNIQUE,
                eco_index FLOAT NOT NULL,
                description TEXT,
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Reinsert the data
            $stmt = $db->prepare("INSERT INTO TRANSPORT_TYPE (nom, eco_index, description) VALUES (?, ?, ?)");
            foreach ($oldData as $row) {
                $stmt->execute([
                    $row['nom'],
                    $row['eco_index'],
                    $row['description'] ?? ''
                ]);
            }
            
            $alertMessage = "<div class='alert alert-info'>Structure de la table des types de transport mise à jour.</div>";
        }
    }
    
    // Modify the query to also select the id field explicitly
    $query = $db->query("SELECT id, nom, eco_index, description FROM TRANSPORT_TYPE ORDER BY eco_index DESC");
    $transportTypes = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les statistiques d'utilisation des transports
    $transportStats = [];
    $transportNames = [];
    $transportCounts = [];
    $transportColors = [];
    
    try {
        $stmt = $db->query("
            SELECT t.type_transport, COUNT(*) as usage_count 
            FROM TRANSPORT t 
            GROUP BY t.type_transport 
            ORDER BY usage_count DESC 
            LIMIT 7
        ");
        $transportStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Préparer les données pour le graphique
        if (!empty($transportStats)) {
            $colorPalette = [
                '#4caf50', '#2196f3', '#ff9800', '#f44336', 
                '#9c27b0', '#673ab7', '#3f51b5', '#009688'
            ];
            
            foreach ($transportStats as $index => $stat) {
                $transportNames[] = $stat['type_transport'];
                $transportCounts[] = $stat['usage_count'];
                $transportColors[] = $colorPalette[$index % count($colorPalette)];
            }
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des statistiques de transport: " . $e->getMessage());
    }
} catch (PDOException $e) {
    $alertMessage = "<div class='alert alert-danger'>Erreur: " . htmlspecialchars($e->getMessage()) . "</div>";
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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* Améliorations du style pour la page de gestion des transports */
    .table-responsive {
        max-height: 500px;
        overflow-y: auto;
        margin-bottom: 2rem;
    }
    
    .table-bordered {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table th {
        position: sticky;
        top: 0;
        background-color: #f8f9fc;
        z-index: 10;
        box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);
    }
    
    .badge {
        padding: 0.5rem 0.75rem;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.8rem;
    }
    
    .btn-info, .btn-danger, .btn-success {
        border-radius: 5px;
        transition: all 0.3s ease;
        padding: 0.375rem 0.75rem;
        font-weight: 500;
    }
    
    .btn-info:hover, .btn-danger:hover, .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
    
    .alert-info {
        background-color: rgba(33, 150, 243, 0.1);
        border-color: #2196f3;
        color: #0d47a1;
    }
    
    .stats-card {
        background-color: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }
    
    .stats-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #eee;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        margin: 0 auto;
        max-width: 500px;
    }
    
    .no-data-message {
        text-align: center;
        padding: 2rem;
        color: #777;
        font-style: italic;
    }
    
    .modal-content {
        border-radius: 8px;
        overflow: hidden;
    }
    
    .modal-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #eee;
    }
    
    .form-control:focus {
        border-color: #4caf50;
        box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
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
        <?= $alertMessage ?>
        
        <?php if (!empty($transportStats)): ?>
        <div class="stats-card">
            <div class="stats-header">
                <h6 class="m-0 font-weight-bold text-primary">Statistiques d'utilisation des transports</h6>
            </div>
            <div class="chart-container">
                <canvas id="transportChart"></canvas>
            </div>
        </div>
        <?php else: ?>
        <div class="stats-card">
            <div class="stats-header">
                <h6 class="m-0 font-weight-bold text-primary">Statistiques d'utilisation des transports</h6>
            </div>
            <div class="no-data-message">
                <p>Aucune donnée d'utilisation disponible.</p>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Types de Transport</h6>
                <a href="/WaveNet/views/backoffice/ajouterTransport.php" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> Ajouter un type
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Éco-index</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transportTypes as $type): ?>
                                <tr>
                                    <td><?= isset($type['id']) ? $type['id'] : '' ?></td>
                                    <td><?= htmlspecialchars($type['nom']) ?></td>
                                    <td>
                                        <?php 
                                        $ecoIndex = floatval($type['eco_index']);
                                        $ecoClass = $ecoIndex >= 7 ? 'success' : ($ecoIndex >= 4 ? 'warning' : 'danger');
                                        ?>
                                        <span class="badge badge-<?= $ecoClass ?>"><?= number_format($ecoIndex, 1) ?>/10</span>
                                    </td>
                                    <td><?= htmlspecialchars($type['description'] ?? '') ?></td>
                                    <td>
                                        <a href="/WaveNet/views/backoffice/modifierTransport.php?id=<?= isset($type['id']) ? $type['id'] : '0' ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                        <a href="/WaveNet/views/backoffice/supprimerTransport.php?id=<?= isset($type['id']) ? $type['id'] : '0' ?>" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
  </main>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function() {
      // Initialize tooltips
      $('[data-toggle="tooltip"]').tooltip();
      
      <?php if (!empty($transportStats)): ?>
      // Créer le graphique en fromage
      var ctx = document.getElementById('transportChart').getContext('2d');
      var transportChart = new Chart(ctx, {
          type: 'pie',
          data: {
              labels: <?= json_encode($transportNames) ?>,
              datasets: [{
                  data: <?= json_encode($transportCounts) ?>,
                  backgroundColor: <?= json_encode($transportColors) ?>,
                  borderWidth: 1
              }]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                  legend: {
                      position: 'right',
                      labels: {
                          font: {
                              family: 'Inter',
                              size: 12
                          }
                      }
                  },
                  tooltip: {
                      callbacks: {
                          label: function(context) {
                              var label = context.label || '';
                              var value = context.raw;
                              var total = context.dataset.data.reduce((a, b) => a + b, 0);
                              var percentage = Math.round((value / total) * 100);
                              return label + ': ' + value + ' utilisations (' + percentage + '%)';
                          }
                      }
                  }
              }
          }
      });
      <?php endif; ?>
    });
  </script>
</body>
</html> 