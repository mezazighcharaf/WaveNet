<?php
include_once "../../Controller/infraC.php";
$infraC = new infraC();
$listeInfrastructures = $infraC->afficherInfrastructure();
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($searchTerm)) {
    $listeInfrastructures = $infraC->rechercherInfrastructureParType($searchTerm);
} else {
    $listeInfrastructures = $infraC->afficherInfrastructure(); 
}
$statsInfra = $infraC->getStatsInfrastructures();
$totalInfra = array_sum(array_column($statsInfra, 'count'));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Infrastructures - WaveNet</title>
    <link rel="stylesheet" href="/WaveNet/views/assets/css/backoffice11.css">
    <link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                <li><a href="/WaveNet/views/backoffice/backinfra.php" class="active"><i class="fas fa-building"></i> Infrastructures</a></li>
                <li><a href="/WaveNet/views/backoffice/gsignalement.php"><i class="fas fa-exclamation-triangle"></i> Signalements</a></li>
                <li><a href="/WaveNet/views/backoffice/interventions.php"><i class="fas fa-tools"></i> Interventions</a></li>
                <li><a href="/WaveNet/views/backoffice/recompenseback.php"><i class="fas fa-gift"></i> Récompenses</a></li>
                <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php"><i class="fas fa-home"></i> Accueil frontoffice</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <div class="content-header">
            <h1>Infrastructures</h1>
            <div>
                <a href="/WaveNet/views/backoffice/ajouterinfra.php" class="btn btn-primary">Ajouter Infrastructure</a>
            </div>
        </div>
        
        <section class="backoffice-section">
            <div class="section-container">
                <div class="section-header">
                    <form method="GET" action="" class="search-form">
                        <input type="text" name="search" placeholder="Rechercher par type..." 
                            value="<?= htmlspecialchars($searchTerm) ?>">
                        <button type="submit" class="btn-search">Rechercher</button>
                        <?php if (!empty($searchTerm)): ?>
                            <a href="/WaveNet/views/backoffice/backinfra.php" class="btn-clear">Effacer</a>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>id_quartier</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (is_array($listeInfrastructures) && count($listeInfrastructures) > 0): ?>
                                <?php foreach ($listeInfrastructures as $infrastructure): ?>
                                <tr>
                                    <td><?= htmlspecialchars($infrastructure['id_infra']) ?></td>
                                    <td><?= htmlspecialchars($infrastructure['type']) ?></td>
                                    <td><?= htmlspecialchars($infrastructure['statut']) ?></td>
                                    <td><?= htmlspecialchars($infrastructure['idq']) ?></td>
                                    <td class="actions-cell">
                                        <a href="/WaveNet/views/backoffice/modifierinfra.php?id=<?= $infrastructure['id_infra'] ?>" class="btn btn-edit">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                        <a href="/WaveNet/views/backoffice/supprimerinfra.php?id=<?= $infrastructure['id_infra'] ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette infrastructure ?')">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="no-data">Aucune infrastructure trouvée.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="backoffice-section">
            <div class="content-header">
                <h2>Statistiques des Infrastructures</h2>
            </div>
            
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-city"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Infrastructures</h3>
                        <p><?= $totalInfra ?></p>
                    </div>
                </div>
                
                <?php foreach ($statsInfra as $stat): ?>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= htmlspecialchars($stat['type']) ?></h3>
                        <p><?= $stat['count'] ?></p>
                        <span class="trend positive"><?= $stat['percentage'] ?>%</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Ajout du graphique circulaire -->
            <div class="chart-container">
                <div class="pie-chart" style="background: conic-gradient(
                    <?php 
                    $total = 0;
                    foreach ($statsInfra as $stat): 
                        $percentage = $stat['percentage'];
                        $color = $infraC->getColorForType($stat['type']);
                        echo "$color $total% " . ($total + $percentage) . '% ,';
                        $total += $percentage;
                    endforeach;
                    ?>
                )"></div>
                <div class="chart-legend">
                    <?php foreach ($statsInfra as $stat): ?>
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: <?= $infraC->getColorForType($stat['type']) ?>"></span>
                        <span><?= htmlspecialchars($stat['type']) ?> (<?= $stat['percentage'] ?>%)</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>
</body>
</html>