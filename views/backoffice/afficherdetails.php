<?php
include_once(__DIR__ . '/../../Controller/quartierC.php');

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $quartierC = new quartierC();
    $quartier = $quartierC->recupererQuartierparId($_GET['id']);

    if (!$quartier) {
        die("Quartier non trouvé.");
    }
} else {
    die("ID du quartier manquant.");
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du Quartier</title>
    <link rel="stylesheet" href="../assets/css/style11.css">
</head>
<body class="frontoffice">
    <header class="main-header">
        <div class="header-container">
            <h1 class="logo">URBAVERSE <span>Quartier</span></h1>
            <nav class="main-nav">
                <ul class="nav-links">
                    <li><a href="frontquartier.php">Retour</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <section class="quartier-details">
            <div class="section-container">
                <h2 class="section-title">Détails du quartier : <?= htmlspecialchars($quartier['nomq']) ?></h2>
                
                <div class="quartier-card">
                    <div class="card-content">
                        <p><strong>ID :</strong> <?= htmlspecialchars($quartier['idq']) ?></p>
                        <p><strong>Nom :</strong> <?= htmlspecialchars($quartier['nomq']) ?></p>
                        <p><strong>Ville :</strong> <?= isset($quartier['ville']) ? htmlspecialchars($quartier['ville']) : '-' ?></p>
                        <p><strong>Score Écologique :</strong> <?= htmlspecialchars($quartier['scoreeco']) ?>/100</p>
                        <p><strong>Classement :</strong> <?= isset($quartier['classement']) ? htmlspecialchars($quartier['classement']) : '-' ?></p>
                        <p><strong>Localisation :</strong> <?= isset($quartier['localisation']) ? htmlspecialchars($quartier['localisation']) : '-' ?></p>
                        <?php if (isset($quartier['latitude']) && isset($quartier['longitude'])): ?>
                            <p><strong>Coordonnées :</strong> <?= htmlspecialchars($quartier['latitude']) ?>, <?= htmlspecialchars($quartier['longitude']) ?></p>
                        <?php endif; ?>
                    </div>
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
