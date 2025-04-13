<?php
var_dump($_GET); // à supprimer une fois que ça marche

include_once(__DIR__ . '/../../Controller/quartierC.php'); // chemin corrigé

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
    <link rel="stylesheet" href="frontquartier.css">
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
                    <div class="card-image">
                        <img src="<?= htmlspecialchars($quartier['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($quartier['nomq']) ?>">
                    </div>
                    <div class="card-content">
                        <p><strong>ID :</strong> <?= htmlspecialchars($quartier['idq']) ?></p>
                        <p><strong>Nom :</strong> <?= htmlspecialchars($quartier['nomq']) ?></p>
                        <p><strong>Ville :</strong> <?= htmlspecialchars($quartier['ville']) ?></p>
                        <p><strong>Score Écologique :</strong> <?= htmlspecialchars($quartier['scoreeco']) ?>/100</p>
                        <p><strong>Classement :</strong> <?= htmlspecialchars($quartier['classement']) ?></p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <!-- Footer -->
    </footer>
</body>
</html>
