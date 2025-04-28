<?php
include_once "../../Controller/infraC.php";
$infraC = new infraC();
$listeInfrastructures = $infraC->afficherInfrastructure();
$statsInfra = $infraC->getStatsInfrastructures();
$totalInfra = array_sum(array_column($statsInfra, 'count'));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Infrastructures - UrbaVerse</title>
    <link rel="stylesheet" href="backinfra.css">
</head>
<body>
    <div class="sidebar">
        <h2>Urbaverse</h2>
        <ul>
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">Signalements</a></li>
            <li><a href="#">Utilisateurs</a></li>
            <li><a href="#">Quartiers</a></li>
            <li><a href="#">Infrastructures</a></li>
            <li><a href="#">Paramètres</a></li>
            <li><a href="#">Retour à l'accueil</a></li>
        </ul>
    </div>

    <div class="main">
        <div class="header">
            <h1>Infrastructures</h1>
            <a href="ajouterinfra.php" class="add-btn">Ajouter Infrastructure</a>
        </div>

        <section class="infrastructures-section">
            <div class="section-container">
                <div class="infrastructures-table-container">
                    <table class="infrastructures-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (is_array($listeInfrastructures) && count($listeInfrastructures) > 0): ?>
                                <?php foreach ($listeInfrastructures as $infrastructure): ?>
                                <tr class="infrastructure-row">
                                    <td><?= htmlspecialchars($infrastructure['id_infra']) ?></td>
                                    <td><?= htmlspecialchars($infrastructure['type']) ?></td>
                                    <td><?= htmlspecialchars($infrastructure['statut']) ?></td>
                                    <td class="actions-cell">
                                        <div class="table-actions">
                                            <a href="modifierinfra.php?id=<?= $infrastructure['id_infra'] ?>" class="btn btn-edit">Modifier</a>
                                            <a href="supprimerinfra.php?id=<?= $infrastructure['id_infra'] ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette infrastructure ?')">Supprimer</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="no-data">Aucune infrastructure trouvée.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <section class="stats-section">
            <div class="section-container">
                <h2>Statistiques des Infrastructures</h2>
                
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-1-5h2v2h-2v-2zm0-8h2v6h-2V7z" fill="currentColor"/></svg>
                        </div>
                        <div class="stat-content">
                            <h3>Total Infrastructures</h3>
                            <p><?= $totalInfra ?></p>
                        </div>
                    </div>
                    
                    <?php foreach ($statsInfra as $stat): ?>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-1-5h2v2h-2v-2zm0-8h2v6h-2V7z" fill="currentColor"/></svg>
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
            </div>
        </section>

    </div>
</body>
</html>