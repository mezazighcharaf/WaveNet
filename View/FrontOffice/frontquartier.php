<?php
include_once "../../Controller/quartierC.php"; // Assure-toi d’avoir cette classe qui gère les requêtes
$quartierC = new quartierC();
$listeQuartiers = $quartierC->afficherQuartier(); // méthode à adapter selon ton code
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>URBAVERSE - Quartiers</title>
    <link rel="stylesheet" href="frontquartier.css">
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
                <h2 class="section-title">Découvrir les Quartiers</h2>
                <div class="quartiers-grid">
                    <?php if (is_array($listeQuartiers) && count($listeQuartiers) > 0): ?>
                        <?php foreach ($listeQuartiers as $quartier): ?>
                        <article class="quartier-card">
                            <div class="card-image">
                                <img src="<?= htmlspecialchars($quartier['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($quartier['nomq']) ?>">
                            </div>
                            <div class="card-content">
                                <h3><?= htmlspecialchars($quartier['nomq']) ?></h3>
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
