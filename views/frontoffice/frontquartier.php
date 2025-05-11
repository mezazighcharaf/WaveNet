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

// Variables pour le header
$pageTitle = 'Découvrir les Quartiers';
$activePage = 'quartiers';

// Inclure Leaflet pour les cartes
$additionalCss = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />';
$additionalScripts = '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>';

// Inclure le header commun
include_once '../includes/userHeader.php';
?>

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

<?php
// Inclure le footer commun  
include_once '../includes/footer.php';
?>
