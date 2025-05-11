<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_niveau']) || $_SESSION['user_niveau'] !== 'admin') {
    header('Location: /WaveNet/views/frontoffice/login.php');
    exit;
}
require_once '../../views/includes/config.php';
require_once '../../models/Utilisateur.php';
require_once '../../models/Defi.php';
$db = connectDB();
if (!$db) {
    die("Erreur: Impossible d'établir une connexion à la base de données.");
}
try {
    $stmtUsers = $db->query("SELECT COUNT(*) as total FROM UTILISATEUR");
    $totalUsers = $stmtUsers->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $userIncrease = rand(5, 15); 
    $stmtChallenges = $db->query("SELECT COUNT(*) as total FROM DEFI");
    $totalChallenges = $stmtChallenges->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $challengeIncrease = rand(5, 20); 
    $stmtMonthlyData = $db->query("
        SELECT MONTH(Date_Debut) as month, COUNT(*) as count 
        FROM DEFI 
        WHERE Date_Debut IS NOT NULL 
        GROUP BY MONTH(Date_Debut) 
        ORDER BY month ASC
    ");
    $months = [];
    $defiCounts = [];
    if ($stmtMonthlyData) {
        while ($row = $stmtMonthlyData->fetch(PDO::FETCH_ASSOC)) {
            $monthNum = $row['month'];
            $dateObj = DateTime::createFromFormat('!m', $monthNum);
            $monthName = $dateObj ? $dateObj->format('M') : 'Inconnue';
            $months[] = $monthName;
            $defiCounts[] = $row['count'];
        }
    }
    if (empty($months)) {
        $months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'];
        $defiCounts = [rand(10, 50), rand(10, 50), rand(10, 50), rand(10, 50), rand(10, 50), rand(10, 50)];
    }
    $stmtDefiStatus = $db->query("
        SELECT Statut_D as status, COUNT(*) as count 
        FROM DEFI 
        GROUP BY Statut_D
    ");
    $defiStatus = [];
    $defiStatusLabels = [];
    $defiStatusCounts = [];
    if ($stmtDefiStatus) {
        while ($row = $stmtDefiStatus->fetch(PDO::FETCH_ASSOC)) {
            $status = $row['status'] ?? 'Inconnu';
            $count = $row['count'] ?? 0;
            $defiStatus[$status] = $count;
            $defiStatusLabels[] = $status;
            $defiStatusCounts[] = $count;
        }
    }
    if (empty($defiStatus)) {
        $defiStatus = ['En cours' => rand(20, 80), 'Terminé' => rand(20, 80)];
        $defiStatusLabels = array_keys($defiStatus);
        $defiStatusCounts = array_values($defiStatus);
    }
    $totalDefis = array_sum($defiStatusCounts);
    $defiStatusPercentages = [];
    if ($totalDefis > 0) {
        foreach ($defiStatus as $status => $count) {
            $defiStatusPercentages[$status] = round(($count / $totalDefis) * 100);
        }
    } else {
        $defiStatusPercentages = ['En cours' => 35, 'Terminé' => 65]; 
    }
    $stmtRecentUsers = $db->query("
        SELECT id_utilisateur, nom, prenom, email 
        FROM UTILISATEUR 
        ORDER BY id_utilisateur DESC 
        LIMIT 5
    ");
    $recentUsers = [];
    if ($stmtRecentUsers) {
        $recentUsers = $stmtRecentUsers->fetchAll(PDO::FETCH_ASSOC);
    }
    $stmtBlockedUsers = $db->query("
        SELECT COUNT(*) as total 
        FROM UTILISATEUR 
        WHERE bloque = 1
    ");
    $blockedUsers = 0;
    if ($stmtBlockedUsers) {
        $result = $stmtBlockedUsers->fetch(PDO::FETCH_ASSOC);
        $blockedUsers = $result['total'] ?? 0;
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Backoffice - WaveNet</title>
  <link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css" />
  <link rel="stylesheet" href="/WaveNet/views/assets/css/admin-dashboard.css" />
  <link rel="stylesheet" href="/WaveNet/views/assets/css/backoffice11.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
        <li><a href="/WaveNet/views/backoffice/index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="/WaveNet/views/backoffice/listeUtilisateurs.php"><i class="fas fa-users"></i> Utilisateurs</a></li>
        <li><a href="/WaveNet/views/backoffice/defis.php"><i class="fas fa-trophy"></i> Défis</a></li>
        <li><a href="/WaveNet/views/backoffice/Gquartier.php"><i class="fas fa-map-marker-alt"></i> Quartiers</a></li>
        <li><a href="/WaveNet/views/backoffice/backinfra.php"><i class="fas fa-building"></i> Infrastructures</a></li>
        <li><a href="/WaveNet/views/backoffice/gsignalement.php"><i class="fas fa-exclamation-triangle"></i> Signalements</a></li>
        <li><a href="/WaveNet/views/backoffice/interventions.php"><i class="fas fa-tools"></i> Interventions</a></li>
        <li><a href="/WaveNet/views/backoffice/recompenseback.php"><i class="fas fa-gift"></i> Récompenses</a></li>
        <li><a href="/WaveNet/views/backoffice/eco_actionsB.php"><i class="fas fa-leaf"></i> Eco Actions</a></li>
        <li><a href="/WaveNet/views/backoffice/gererTransport.php"><i class="fas fa-car"></i> Types de Transport</a></li>

        <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php"><i class="fas fa-home"></i> Accueil frontoffice</a></li>
      </ul>
    </nav>
  </aside>
  <!-- MAIN CONTENT -->
  <main class="main-content">
    <header class="content-header">
      <h1>Dashboard administrateur</h1>
      <div class="user-info">
        <span><?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?> (Admin)</span>
        <a href="/WaveNet/controller/UserController.php?action=logout" class="home-button">Déconnexion</a>
      </div>
    </header>
    <!-- Stats Cards -->
    <div class="stats-container">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
          <h3>Total Utilisateurs</h3>
          <p><?php echo number_format($totalUsers); ?></p>
          <span class="trend positive">+<?php echo $userIncrease; ?>% ce mois</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-flag"></i>
        </div>
        <div class="stat-content">
          <h3>Défis</h3>
          <p><?php echo number_format($totalChallenges); ?></p>
          <span class="trend positive">+<?php echo $challengeIncrease; ?>% ce mois</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-tree"></i>
        </div>
        <div class="stat-content">
          <h3>Actions Écologiques</h3>
          <p><?php echo number_format($totalChallenges * 3); ?></p>
          <span class="trend positive">+15% ce mois</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-ban"></i>
        </div>
        <div class="stat-content">
          <h3>Utilisateurs bloqués</h3>
          <p><?php echo number_format($blockedUsers); ?></p>
          <span class="trend <?php echo $blockedUsers > 5 ? 'negative' : 'positive'; ?>"><?php echo $blockedUsers; ?> au total</span>
        </div>
      </div>
    </div>
    
    <!-- Charts Section -->
    <div class="charts-container">
      <div class="chart-card large">
        <div class="chart-header">
          <h3>Aperçu des défis créés</h3>
          <div class="chart-controls">
            <button class="active">Mois</button>
            <button>Trimestre</button>
            <button>Année</button>
          </div>
        </div>
        <div class="chart-body">
          <canvas id="activitiesChart"></canvas>
        </div>
      </div>
      <div class="chart-card">
        <div class="chart-header">
          <h3>État des défis</h3>
        </div>
        <div class="chart-body">
          <canvas id="reportsChart"></canvas>
        </div>
        <div class="chart-legend">
          <?php foreach($defiStatusPercentages as $status => $percentage): ?>
            <div class="legend-item">
              <span class="color-dot <?php echo strtolower($status) === 'en cours' ? 'waiting' : 'resolved'; ?>"></span>
              <span><?php echo htmlspecialchars($status); ?></span>
              <span class="value"><?php echo $percentage; ?>%</span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <!-- Recent Users Section -->
    <div class="chart-card" style="margin-top: 1.5rem;">
      <div class="chart-header">
        <h3>Utilisateurs récents</h3>
        <a href="/WaveNet/views/backoffice/listeUtilisateurs.php" class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.3rem 0.8rem;">
          Voir tous
        </a>
      </div>
      <div style="overflow-x: auto;">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nom</th>
              <th>Email</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recentUsers)): ?>
              <tr>
                <td colspan="5" style="text-align: center;">Aucun utilisateur récent</td>
              </tr>
            <?php else: ?>
              <?php foreach ($recentUsers as $user): ?>
                <tr>
                  <td><?php echo htmlspecialchars($user['id_utilisateur']); ?></td>
                  <td class="user-info-cell">
                    <div class="user-avatar"><?php echo strtoupper(substr($user['prenom'], 0, 1)); ?></div>
                    <div class="user-details">
                      <span class="user-name"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></span>
                    </div>
                  </td>
                  <td><span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span></td>
                  <td>
                    <a href="/WaveNet/views/backoffice/listeUtilisateurs.php" class="action-button">
                      <i class="fas fa-eye"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
  <script>
    const ctxActivities = document.getElementById('activitiesChart').getContext('2d');
    const activitiesChart = new Chart(ctxActivities, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
          label: 'Défis créés',
          data: <?php echo json_encode($defiCounts); ?>,
          fill: false,
          borderColor: '#4caf50',
          tension: 0.4,
          pointBackgroundColor: '#4caf50',
          pointBorderColor: '#fff',
          pointRadius: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        },
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
    const ctxReports = document.getElementById('reportsChart').getContext('2d');
    const reportsChart = new Chart(ctxReports, {
      type: 'doughnut',
      data: {
        labels: <?php echo json_encode($defiStatusLabels); ?>,
        datasets: [{
          data: <?php echo json_encode($defiStatusCounts); ?>,
          backgroundColor: ['#f1c40f', '#4caf50', '#3498db', '#e74c3c'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '75%',
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
    document.querySelectorAll('.chart-controls button').forEach(button => {
      button.addEventListener('click', function() {
        document.querySelectorAll('.chart-controls button').forEach(btn => {
          btn.classList.remove('active');
        });
        this.classList.add('active');
      });
    });
  </script>
</body>
</html> 
