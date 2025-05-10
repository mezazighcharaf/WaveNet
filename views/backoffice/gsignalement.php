<?php
session_start();

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page.";
    header('Location: /WaveNet/views/frontoffice/login.php');
    exit;
}

// Vérification du niveau d'utilisateur (admin a accès direct, client est redirigé)
$userNiveau = $_SESSION['user_niveau'] ?? 'client';
if ($userNiveau !== 'admin') {
    header('Location: /WaveNet/views/frontoffice/userDashboard.php');
    exit;
}

include_once '../../controller/signalementctrl.php';
include_once '../../views/includes/config.php';

$signalementC = new Signalementc();
$liste = $signalementC->afficherSignalement();


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $signalementC->deleteSignalement($_POST['delete_id']);
    
    header('Location: /WaveNet/views/backoffice/gsignalement.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Backoffice - Signalements - WaveNet</title>
  <link rel="stylesheet" href="/WaveNet/views/assets/css/backoffice11.css" />
  <link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" type="text/css">
  
  <meta name="description" content="Gestion des signalements - Backoffice WaveNet">
</head>
<body>
  
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
            <li><a href="/WaveNet/views/backoffice/gsignalement.php" class="active"><i class="fas fa-exclamation-triangle"></i> Signalements</a></li>
            <li><a href="/WaveNet/views/backoffice/interventions.php"><i class="fas fa-tools"></i> Interventions</a></li>
            <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php"><i class="fas fa-home"></i> Accueil frontoffice</a></li>
        </ul>
    </nav>
  </aside>

  
  <main class="main-content">
    <header class="content-header">
      <h1>Gestion des Signalements</h1>
      <div class="user-info">
        <span>Admin</span>
        <a href="/WaveNet/controller/UserController.php?action=logout" class="logout-link"><i class="fas fa-sign-out-alt"></i></a>
      </div>
    </header>

    
    <div class="container-fluid">
        <div class="card shadow mb-4" style="background: linear-gradient(135deg, #e8f0ef 0%, #f9fafc 100%); border: none; box-shadow: 0 4px 24px #2e4f3e22;">
            <div class="card-header py-3" style="background: transparent; border-bottom: none;">
                <h5 class="m-0 font-weight-bold" style="color: #2e4f3e; font-size: 1.4rem; letter-spacing: 1px;">📊 Statistiques par type d'anomalie</h5>
            </div>
            <div class="card-body" style="padding: 2.5rem 2rem 2rem 2rem;">
                <canvas id="chartType" style="max-height: 340px;"></canvas>
            </div>
        </div>
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Liste des Signalements</h6>
                <a href="/WaveNet/views/backoffice/addsignalement.php" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Ajouter
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Description</th>
                                <th>Emplacement</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($liste as $signalement): ?>
                                <tr>
                                <td><?= htmlspecialchars($signalement['id_signalement']) ?></td>
                                <td><?= htmlspecialchars($signalement['titre']) ?></td>
                                <td><?= htmlspecialchars(substr($signalement['description'], 0, 50)) . (strlen($signalement['description']) > 50 ? '...' : '') ?></td>
                                <td><?= htmlspecialchars($signalement['emplacement']) ?></td>
                                <td><?= htmlspecialchars($signalement['date_signalement']) ?></td>
                                <td><span class="badge badge-<?= $signalement['statut'] == 'traité' ? 'success' : ($signalement['statut'] == 'en cours' ? 'warning' : 'danger') ?>"><?= htmlspecialchars($signalement['statut']) ?></span></td>
                                <td>
                                    
                                    <form action="/WaveNet/views/backoffice/gsignalement.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="delete_id" value="<?= htmlspecialchars($signalement['id_signalement']) ?>" />
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce signalement ?');"><i class="fas fa-trash"></i></button>
                                    </form>
                                    
                                    <a href="/WaveNet/views/backoffice/modifiersignalement.php?id=<?= htmlspecialchars($signalement['id_signalement']) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    
                                    <a href="/WaveNet/views/backoffice/afficherintervention.php?signalement=<?= htmlspecialchars($signalement['id_signalement']) ?>" class="btn btn-info btn-sm"><i class="fas fa-tasks"></i></a>
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
  <script src="/WaveNet/views/backoffice/js/sb-admin-2.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <?php
  // Statistiques par type d'anomalie
  $stats = [];
  foreach ($liste as $s) {
      $type = $s['titre'];
      if (!isset($stats[$type])) $stats[$type] = 0;
      $stats[$type]++;
  }
  $labels = json_encode(array_keys($stats));
  $values = json_encode(array_values($stats));
  // Couleurs variées pour chaque barre
  $colors = ['#2e4f3e','#4caf50','#f1c40f','#e67e22','#e74c3c','#2980b9','#8e44ad','#16a085','#f39c12','#d35400'];
  $barColors = [];
  $i = 0;
  foreach ($stats as $k=>$v) {
      $barColors[] = $colors[$i % count($colors)];
      $i++;
  }
  $barColors = json_encode($barColors);
  ?>
  <script>
  const ctx = document.getElementById('chartType').getContext('2d');
  const chart = new Chart(ctx, {
      type: 'bar',
      data: {
          labels: <?php echo $labels; ?>,
          datasets: [{
              label: 'Nombre de signalements',
              data: <?php echo $values; ?>,
              backgroundColor: <?php echo $barColors; ?>,
              borderColor: <?php echo $barColors; ?>,
              borderWidth: 2,
              borderRadius: 8,
              hoverBackgroundColor: '#263f32',
              hoverBorderColor: '#263f32',
          }]
      },
      options: {
          responsive: true,
          plugins: {
              legend: {
                  display: true,
                  labels: {
                      color: '#2e4f3e',
                      font: { size: 15, weight: 'bold' },
                      padding: 20
                  },
                  position: 'top',
                  align: 'end'
              },
              title: {
                  display: false
              },
              tooltip: {
                  backgroundColor: '#2e4f3e',
                  titleColor: '#fff',
                  bodyColor: '#fff',
                  borderColor: '#4caf50',
                  borderWidth: 1
              }
          },
          scales: {
              x: {
                  grid: { display: false },
                  ticks: { color: '#2e4f3e', font: { size: 14, weight: 'bold' } }
              },
              y: {
                  beginAtZero: true,
                  grid: { color: '#e8f0ef' },
                  ticks: { color: '#2e4f3e', font: { size: 13 } }
              }
          },
          animation: {
              duration: 1200,
              easing: 'easeOutBounce'
          }
      }
  });
  </script>
</body>
</html>