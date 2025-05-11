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

// Variables pour le header
$pageTitle = 'Découvrir les Infrastructures';
$activePage = 'infrastructures';

// Inclure le header commun
include_once '../includes/userHeader.php';
?>

<main class="main-content">
    <section class="infrastructures-section"> 
        <div class="section-container">
            <div class="section-header">
                <h2 class="section-title">Découvrir les Infrastructures</h2> 
                <form method="GET" action="" class="search-form">
                    <input type="text" name="search" placeholder="Rechercher par type..." 
                        value="<?= htmlspecialchars($searchTerm) ?>">
                    <button type="submit" class="btn-search">Rechercher</button>
                    <?php if (!empty($searchTerm)): ?>
                        <a href="frontinfra.php" class="btn-clear">Effacer</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="infrastructures-grid"> 
                <?php if (is_array($listeInfrastructures) && count($listeInfrastructures) > 0): ?>
                    <?php foreach ($listeInfrastructures as $infrastructure): ?>
                        <article class="infrastructure-card"> 
                            <div class="card-image">
                                <img src="<?= htmlspecialchars($infrastructure['image'] ?? 'default-infra.jpg') ?>" alt="<?= htmlspecialchars($infrastructure['type']) ?>">
                            </div>
                            <div class="card-content">
                                <h3><?= htmlspecialchars($infrastructure['type']) ?></h3>
                                <p class="infrastructure-status"> 
                                    Statut: <span class="status-<?= strtolower($infrastructure['statut']) ?>">
                                        <?= htmlspecialchars($infrastructure['statut']) ?>
                                    </span>
                                </p>
                                <div class="card-actions">
                                    <a href="afficherdetailsinfra.php?id=<?= $infrastructure['id_infra'] ?>" class="btn btn-details">Détails</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-results">Aucune infrastructure trouvée<?= !empty($searchTerm) ? ' pour "'.htmlspecialchars($searchTerm).'"' : '' ?>.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php
// Inclure le footer commun
include_once '../includes/footer.php';
?>