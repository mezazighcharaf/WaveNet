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
    <title>URBAVERSE - Quartiers</title>
    <link rel="stylesheet" href="frontquartier.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

</head>
<body class="frontoffice">
<header class="main-header">
        <div class="header-container">
            <h1 class="logo">URBAVERSE </h1>
            <nav class="main-nav">
                <ul class="nav-links">
                    <li><a href="#">Tableau de bord</a></li>
                    <li><a href="#" class="active">Quartiers</a></li>
                    <li><a href="#">Infrastructures</a></li>
                    <li><a href="#">Utilisateurs</a></li>
                    <li><a href="#">Statistiques</a></li>
                    <li><a href="#" class="btn-logout">Déconnexion</a></li>
                </ul>
            </nav>
        </div>
    </header>


    <main class="main-content">
        <section class="quartiers-section">
            <div class="section-container">
                <div class="section-header">
                    <h2 class="section-title">Découvrir les Quartiers</h2>
                    <div class="search-sort-container">
                        <form method="GET" action="" class="search-form">
                            <input type="text" name="search" placeholder="Rechercher par nom..." 
                                value="<?= htmlspecialchars($searchTerm) ?>">
                            <button type="submit" class="btn-search">Rechercher</button>
                            <?php if (!empty($searchTerm)): ?>
                                <a href="frontquartier.php" class="btn-clear">Effacer</a>
                            <?php endif; ?>
                        </form>
                        <a href="frontquartier.php?sort=rank" class="btn-sort <?= $sortByRank ? 'active' : '' ?>">
                            Trier par classement
                        </a>
                    </div>
                </div>
                <div class="quartiers-grid">
                    <?php if (is_array($listeQuartiers) && count($listeQuartiers) > 0): ?>
                        <?php foreach ($listeQuartiers as $quartier): ?>
                            <article class="quartier-card">
                                
                                <div class="card-content">
                                    <h3><?= htmlspecialchars($quartier['nomq']) ?></h3>
                                    <?php if (!empty($quartier['latitude']) && !empty($quartier['longitude'])): ?>
                                        <div id="map-<?= $quartier['idq'] ?>" style="height: 300px; margin-top: 10px;"></div>
                                        <script>
                                            var map<?= $quartier['idq'] ?> = L.map('map-<?= $quartier['idq'] ?>').setView([<?= $quartier['latitude'] ?>, <?= $quartier['longitude'] ?>], 17);
                                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                                maxZoom: 19,
                                            }).addTo(map<?= $quartier['idq'] ?>);
                                            L.marker([<?= $quartier['latitude'] ?>, <?= $quartier['longitude'] ?>])
                                                .addTo(map<?= $quartier['idq'] ?>)
                                                .bindPopup("<?= htmlspecialchars($quartier['nomq']) ?>").openPopup();
                                        </script>
                                    <?php else: ?>
                                        <p>Localisation non disponible</p>
                                    <?php endif; ?>


                                    <div class="card-actions">
                                        <a href="afficherdetails.php?id=<?= $quartier['idq'] ?>" class="btn btn-details">Détails</a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun quartier disponible pour le moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <div class="footer-content">
                <div class="footer-section">
                    <h3>URBAVERSE</h3>
                    <p>Innovons ensemble pour des infrastructures urbaines durables et intelligentes.</p>
                </div>
                <div class="footer-section">
                    <h3>LIENS RAPIDES</h3>
                    <ul>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Confidentialité</a></li>
                        <li><a href="#">Conditions</a></li>
                        <li><a href="#">Backoffice</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>SUIVEZ-NOUS</h3>
                    <ul>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">Facebook</a></li>
                        <li><a href="#">Instagram</a></li>
                        <li><a href="#">LinkedIn</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>© 2025 URBAVERSE. Tous droits réservés.</p>
            </div>

    </footer>
</body>
</html>
