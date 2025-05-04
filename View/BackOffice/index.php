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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Quartiers - UrbaVerse</title>
    <link rel="stylesheet" href="index.css">
    <style>
        .btn-map {
            display: inline-block;
            margin-left: 10px;
            padding: 2px 8px;
            background-color: #4285F4;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-map:hover {
            background-color: #3367D6;
        }
        .location-cell {
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Urbaverse</h2>
        <ul>
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">Signalements</a></li>
            <li><a href="#">Utilisateurs</a></li>
            <li><a href="#">Quartiers</a></li>
            <li><a href="#">Paramètres</a></li>
            <li><a href="#">Retour à l'accueil</a></li>
        </ul>
    </div>

    <div class="main">
        <div class="header">
            <h1>Quartiers</h1>
            <a href="ajouterquartier.php" class="add-btn">Ajouter Quartier</a>
        </div>

        <section class="quartiers-section">
            <div class="section-container">
                <div class="section-header">
                    <div class="search-sort-container">
                        <form method="GET" action="" class="search-form">
                            <input type="text" name="search" placeholder="Rechercher par nom..." 
                                value="<?= htmlspecialchars($searchTerm) ?>">
                            <button type="submit" class="btn-search">Rechercher</button>
                            <?php if (!empty($searchTerm)): ?>
                                <a href="index.php" class="btn-clear">Effacer</a>
                            <?php endif; ?>
                        </form>
                        <a href="index.php?sort=rank" class="btn-sort <?= $sortByRank ? 'active' : '' ?>">
                            Trier par classement
                        </a>
                    </div>
                </div>
                <div class="quartiers-table-container">
                    <table class="quartiers-table">
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
                                <tr class="quartier-row">
                                    <td><?= htmlspecialchars($quartier['idq']) ?></td>
                                    <td><?= htmlspecialchars($quartier['nomq']) ?></td>
                                    <td><?= htmlspecialchars($quartier['ville']) ?></td>
                                    <td><?= htmlspecialchars($quartier['scoreeco']) ?>/100</td>
                                    <td><?= htmlspecialchars($quartier['classement']) ?></td>
                                    <td><?= htmlspecialchars($quartier['localisation']) ?></td>
                                    <td><?= htmlspecialchars($quartier['latitude']) ?></td>
                                    <td><?= htmlspecialchars($quartier['longitude']) ?></td>
                                    
                                    <td class="actions-cell">
                                        <div class="table-actions">
                                            <a href="modifierquartier.php?id=<?= $quartier['idq'] ?>" class="btn btn-edit">Modifier</a>
                                            <a href="supprimerquartier.php?id=<?= $quartier['idq'] ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce quartier ?')">Supprimer</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="no-data">Aucun quartier trouvé.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</body>
</html>