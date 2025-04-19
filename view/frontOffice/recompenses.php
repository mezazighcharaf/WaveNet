<?php
require_once(__DIR__ . '/../../controller/partenaireController.php');
require_once(__DIR__ . '/../../controller/recompenseController.php');

$recController = new RecompenseController();
$recompenses = $recController->listAll();
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Récompenses - Urbaverse</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <header class="main-header">
        <nav class="nav-container">
            <div class="logo">
                <h1>Urbaverse</h1>
            </div>
            <ul class="nav-links">
                <li><a href="index.html">Accueil</a></li>
                <li><a href="utilisateurs.html">utilisateurs</a></li>
                <li><a href="Infrastructures.html">Infrastructures</a></li>
                <li><a href="defis.html">défis</a></li>
                <li><a href="eco-actions.html">éco-actions</a></li>
                <li><a href="recompenses.php" class="active">récompenses</a></li>
                <li><a href="signalements.html">signalements</a></li>
            </ul>
            <div class="user-actions">
                <span class="points">Points verts: 150</span>
                <a href="#login" class="btn btn-secondary">Connexion</a>
            </div>
        </nav>
    </header>

    <main class="recompenses">
        <div class="container">
            <div class="recompenses-header">
                <h2>Nos récompenses</h2>
                <p>
                    Transformez vos actions écoresponsables en avantages concrets pour vous et votre communauté.
                </p>
            </div>

            <div class="recompenses-cards">
                <?php foreach ($recompenses as $rec): ?>
                <div class="recompenses-card">
                    <img src="https://picsum.photos/600/400?random=<?= $rec->getIdRec() ?>&nature=1" alt="<?= htmlspecialchars($rec->getNomRec()) ?>">
                    <div class="card-content">
                        <h3><?= htmlspecialchars($rec->getNomRec()); ?></h3>
                        <h4>Coût: <?= htmlspecialchars($rec->getCout()); ?> points verts</h4>
                        <p>
                            <?= htmlspecialchars($rec->getDescription()); ?>
                        </p>
                        <p>
                            👉 Valable jusqu'au <?= date('d/m/Y', strtotime($rec->getDateFin())); ?>
                        </p>
                        <a
                            href="#lire-plus"
                            class="btn btn-primary"
                            style="margin-top: 0.75rem"
                            >Confirmer</a
                        >
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4>Urbaverse</h4>
                <p>Ensemble pour un avenir urbain durable</p>
            </div>
            <div class="footer-section">
                <h4>Liens Rapides</h4>
                <ul>
                    <li><a href="about.html">À propos</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="#privacy">Confidentialité</a></li>
                    <li><a href="#terms">Conditions</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Suivez-nous</h4>
                <div class="social-links">
                    <a href="#twitter">Twitter</a>
                    <a href="#facebook">Facebook</a>
                    <a href="#instagram">Instagram</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Urbaverse. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>