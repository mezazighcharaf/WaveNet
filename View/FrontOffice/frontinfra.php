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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>URBAVERSE - Infrastructures</title>
    <link rel="stylesheet" href="frontinfra.css"> 
</head>
<body class="frontoffice">
<header class="main-header">
        <div class="header-container">
            <h1 class="logo">URBAVERSE</h1>
            <nav class="main-nav">
                <ul class="nav-links">
                    <li><a href="#">Tableau de bord</a></li>
                    <li><a href="#">Quartiers</a></li>
                    <li><a href="#" class="active">Infrastructures</a></li> 
                    <li><a href="#">Utilisateurs</a></li>
                    <li><a href="#">Statistiques</a></li>
                    <li><a href="#" class="btn-logout">Déconnexion</a></li>
                </ul>
            </nav>
        </div>
    </header>

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