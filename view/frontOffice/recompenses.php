<?php
require_once(__DIR__ . '/../../controller/partenaireController.php');
require_once(__DIR__ . '/../../controller/recompenseController.php');
require_once(__DIR__ . '/../../controller/utilisateurController.php');

$recController = new RecompenseController();
$recompenses = $recController->listAll();
$utilisateurController = new UtilisateurController();

// ID de l'utilisateur connecté (à remplacer par la session réelle)
$id_utilisateur_connecte = 123 ; // Exemple - à remplacer par $_SESSION['id_utilisateur'] ou autre
$utilisateur = $utilisateurController->getUtilisateurById($id_utilisateur_connecte);

// Traitement de la confirmation de récompense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_rec'])) {
    $id_rec = $_POST['id_rec'];
    try {
        $recompense = $recController->read($id_rec);
        $cout = $recompense->getCout();
        
        // Vérifier si l'utilisateur a assez de points
        if ($utilisateur->getPointsVerts() >= $cout) {
            $nouveaux_points = $utilisateur->getPointsVerts() - $cout;
            $utilisateurController->updatePointsVerts($id_utilisateur_connecte, $nouveaux_points);
            $utilisateur->setPointsVerts($nouveaux_points);
            
            // Message de succès
            $message = "Récompense acquise avec succès!";
        } else {
            $message = "Points insuffisants pour cette récompense.";
        }
    } catch (Exception $e) {
        $message = "Erreur: " . $e->getMessage();
    }
}

// Fonction pour déterminer si une récompense est en promotion (7 derniers jours)
function isPromo($dateFin) {
    $now = new DateTime();
    $endDate = new DateTime($dateFin);
    $interval = $now->diff($endDate);
    return $interval->days <= 7 && $interval->invert == 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Récompenses - Urbaverse</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="promo-styles.css" />
    <style>
        .alert {
            padding: 15px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 4px;
            text-align: center;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<script>
(function(){if(!window.chatbase||window.chatbase("getState")!=="initialized"){window.chatbase=(...arguments)=>{if(!window.chatbase.q){window.chatbase.q=[]}window.chatbase.q.push(arguments)};window.chatbase=new Proxy(window.chatbase,{get(target,prop){if(prop==="q"){return target.q}return(...args)=>target(prop,...args)}})}const onLoad=function(){const script=document.createElement("script");script.src="https://www.chatbase.co/embed.min.js";script.id="N9VVpJ57n-tJQCTWSzGgc";script.domain="www.chatbase.co";document.body.appendChild(script)};if(document.readyState==="complete"){onLoad()}else{window.addEventListener("load",onLoad)}})();
</script>
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
                <span class="points">Points verts: <?= htmlspecialchars($utilisateur->getPointsVerts()) ?></span>
                <a href="#login" class="btn btn-secondary">Connexion</a>
            </div>
        </nav>
    </header>

    <main class="recompenses">
        <?php if (!empty($message)): ?>
        <div class="alert <?= strpos($message, 'Erreur') !== false || strpos($message, 'insuffisants') !== false ? 'alert-error' : 'alert-success' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>
        
        <div class="container">
            <div class="recompenses-header">
                <h2>Nos récompenses</h2>
                <p>
                    Transformez vos actions écoresponsables en avantages concrets pour vous et votre communauté.
                </p>
            </div>

            <div class="recompenses-cards">
                <?php foreach ($recompenses as $rec): 
                    $isPromo = isPromo($rec->getDateFin());
                    $dateFin = $rec->getDateFin();
                ?>
                <div class="recompenses-card <?= $isPromo ? 'promo-card' : '' ?>">
                    <?php if ($isPromo): ?>
                    <div class="promo-badge">DERNIERE CHANCE!</div>
                    <?php endif; ?>
                    <img src="https://picsum.photos/600/400?random=<?= $rec->getIdRec() ?>&nature=1" alt="<?= htmlspecialchars($rec->getNomRec()) ?>">
                    <div class="card-content">
                        <h3><?= htmlspecialchars($rec->getNomRec()); ?></h3>
                        <h4>Coût: <?= htmlspecialchars($rec->getCout()); ?> points verts</h4>
                        <p>
                            <?= htmlspecialchars($rec->getDescription()); ?>
                        </p>
                        <p>
                            👉 Valable jusqu'au <?= date('d/m/Y', strtotime($dateFin)); ?>
                        </p>
                        <?php if ($isPromo): ?>
                        <div class="countdown" data-end-date="<?= $dateFin ?>"></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <input type="hidden" name="id_rec" value="<?= $rec->getIdRec() ?>">
                            <button type="submit" class="btn btn-primary" style="margin-top: 0.75rem" 
                                <?= $utilisateur->getPointsVerts() < $rec->getCout() ? 'disabled' : '' ?>>
                                Confirmer
                            </button>
                            <?php if ($utilisateur->getPointsVerts() < $rec->getCout()): ?>
                            <p class="points-insuffisants">Vous n'avez pas assez de points pour cette récompense</p>
                            <?php endif; ?>
                        </form>
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

    <script src="promo-script.js"></script>
</body>
</html>