<?php
include_once(__DIR__ . '/../../Controller/infraC.php');

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $infraC = new infraC();
    $infrastructure = $infraC->recupererInfrastructureParId($_GET['id']);

    if (!$infrastructure) {
        die("Infrastructure non trouvée.");
    }
} else {
    die("ID de l'infrastructure manquant.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de l'Infrastructure</title>
    <link rel="stylesheet" href="frontquartier.css">
    <style>
        .infra-card {
            display: flex;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin: 20px 0;
            overflow: hidden;
        }
        .card-content {
            padding: 20px;
            flex: 1;
        }
        .card-image {
            width: 300px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body class="frontoffice">
    <header class="main-header">
        <div class="header-container">
            <h1 class="logo">URBAVERSE <span>Infrastructure</span></h1>
            <nav class="main-nav">
                <ul class="nav-links">
                    <li><a href="frontinfra.php">Retour</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <section class="infra-details">
            <div class="section-container">
                <h2 class="section-title">Détails de l'infrastructure : <?= htmlspecialchars($infrastructure['type']) ?></h2>
                
                <div class="infra-card">
                    <div class="card-image">
                        <img src="assets/infra-<?= htmlspecialchars(strtolower($infrastructure['type'])) ?>.jpg" 
                            alt="<?= htmlspecialchars($infrastructure['type']) ?>">
                    </div>
                    <div class="card-content">
                        <p><strong>ID :</strong> <?= htmlspecialchars($infrastructure['id_infra']) ?></p>
                        <p><strong>Type :</strong> <?= htmlspecialchars($infrastructure['type']) ?></p>
                        <p><strong>Statut :</strong> <?= htmlspecialchars($infrastructure['statut']) ?></p>
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