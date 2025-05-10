<?php
include_once '../../controller/signalementctrl.php';
include_once '../../config.php';

$signalementC = new SignalementC();
$liste = $signalementC->afficherSignalement();

// Calcul des statistiques par type d'anomalie (titre)
$stats = [];
foreach ($liste as $s) {
    $type = $s['titre'];
    if (!isset($stats[$type])) $stats[$type] = 0;
    $stats[$type]++;
}
$labels = json_encode(array_keys($stats));
$values = json_encode(array_values($stats));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques des Signalements - WaveNet</title>
    <link rel="stylesheet" href="/WaveNet/views/assets/css/backoffice11.css" />
    <link rel="stylesheet" href="/WaveNet/views/assets/css/admin-dashboard.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li><a href="/WaveNet/views/backoffice/gsignalement.php"><i class="fas fa-exclamation-triangle"></i> Signalements</a></li>
                <li><a href="/WaveNet/views/backoffice/interventions.php"><i class="fas fa-tools"></i> Interventions</a></li>
                <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php"><i class="fas fa-home"></i> Accueil frontoffice</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="content-header">
            <h1>Statistiques des Signalements</h1>
            <div class="user-info">
                <span>Admin</span>
                <a href="/WaveNet/controller/UserController.php?action=logout" class="logout-link"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <div class="container-fluid">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistiques par type d'anomalie</h6>
                </div>
                <div class="card-body">
                    <canvas id="chartType"></canvas>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        const ctx = document.getElementById('chartType').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo $labels; ?>,
                datasets: [{
                    label: 'Nombre de signalements',
                    data: <?php echo $values; ?>,
                    backgroundColor: 'rgba(46, 79, 62, 0.7)',
                    borderColor: 'rgba(46, 79, 62, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html> 