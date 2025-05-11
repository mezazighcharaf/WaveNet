<?php
include_once "../../Controller/quartierC.php";
$quartierC = new quartierC();
$listeQuartiers = $quartierC->afficherQuartier();
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($searchTerm)) {
    $listeQuartiers= $quartierC->rechercherQuartierParNom($searchTerm);
} else {
    $listeQuartiers = $quartierC->afficherQuartier(); 
}
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($searchTerm)) {
    $listeQuartiers= $quartierC->rechercherQuartierParNom($searchTerm);
} else {
    $listeQuartiers = $quartierC->afficherQuartier(); 
}
$sortByRank = isset($_GET['sort']) && $_GET['sort'] == 'rank';

if (!empty($searchTerm)) {
    $listeQuartiers = $quartierC->rechercherQuartierParNom($searchTerm);
} else {
    $listeQuartiers = $quartierC->afficherQuartier(); 
}

// Trier par classement si demandé
if ($sortByRank && is_array($listeQuartiers)) {
    usort($listeQuartiers, function($a, $b) {
        return $a['classement'] <=> $b['classement'];
    });
}

// Variables pour le header
$pageTitle = 'Gestion des Quartiers';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Quartiers - WaveNet</title>
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
                <li><a href="/WaveNet/views/backoffice/Gquartier.php" class="active"><i class="fas fa-map-marker-alt"></i> Quartiers</a></li>
                <li><a href="/WaveNet/views/backoffice/backinfra.php"><i class="fas fa-building"></i> Infrastructures</a></li>
                <li><a href="/WaveNet/views/backoffice/gsignalement.php"><i class="fas fa-exclamation-triangle"></i> Signalements</a></li>
                <li><a href="/WaveNet/views/backoffice/interventions.php"><i class="fas fa-tools"></i> Interventions</a></li>
                <li><a href="/WaveNet/views/backoffice/recompenseback.php"><i class="fas fa-gift"></i> Récompenses</a></li>
                <li><a href="/WaveNet/views/backoffice/eco_actionsB.php"><i class="fas fa-leaf"></i> Eco Actions</a></li>
                <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php"><i class="fas fa-home"></i> Accueil frontoffice</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <div class="content-header">
            <h1>Gestion des Quartiers</h1>
            <div>
                <a href="/WaveNet/views/backoffice/ajouterquartier.php" class="btn btn-primary">Ajouter Quartier</a>
            </div>
        </div>

        <section class="backoffice-section">
            <div class="section-container">
                <div class="section-header">
                    <div class="search-sort-container">
                        <form method="GET" action="" class="search-form">
                            <input type="text" name="search" placeholder="Rechercher par nom..." 
                                value="<?= htmlspecialchars($searchTerm) ?>">
                            <button type="submit" class="btn-search">Rechercher</button>
                            <?php if (!empty($searchTerm)): ?>
                                <a href="/WaveNet/views/backoffice/Gquartier.php" class="btn-clear">Effacer</a>
                            <?php endif; ?>
                        </form>
                        <a href="/WaveNet/views/backoffice/Gquartier.php?sort=rank" class="btn-sort <?= $sortByRank ? 'active' : '' ?>">
                            Trier par classement
                        </a>
                    </div>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Ville</th>
                                <th>Score Eco</th>
                                <th>Classement</th>
                                <th>Localisation</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (is_array($listeQuartiers) && count($listeQuartiers) > 0): ?>
                                <?php foreach ($listeQuartiers as $quartier): ?>
                                <tr>
                                    <td><?= htmlspecialchars($quartier['idq']) ?></td>
                                    <td><?= htmlspecialchars($quartier['nomq']) ?></td>
                                    <td><?= isset($quartier['ville']) ? htmlspecialchars($quartier['ville']) : '-' ?></td>
                                    <td><?= htmlspecialchars($quartier['scoreeco']) ?>/100</td>
                                    <td><?= isset($quartier['classement']) ? htmlspecialchars($quartier['classement']) : '-' ?></td>
                                    <td><?= isset($quartier['localisation']) ? htmlspecialchars($quartier['localisation']) : '-' ?></td>
                                    <td><?= isset($quartier['latitude']) ? htmlspecialchars($quartier['latitude']) : '-' ?></td>
                                    <td><?= isset($quartier['longitude']) ? htmlspecialchars($quartier['longitude']) : '-' ?></td>
                                    
                                    <td class="actions-cell">
                                        <a href="/WaveNet/views/backoffice/modifierquartier.php?id=<?= $quartier['idq'] ?>" class="btn btn-edit">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                        <a href="/WaveNet/views/backoffice/supprimerquartier.php?id=<?= $quartier['idq'] ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce quartier ?')">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="no-data">Aucun quartier trouvé.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</body>
</html>