<?php
require_once(__DIR__ . '/../../controller/partenaireController.php');
require_once(__DIR__ . '/../../controller/recompenseController.php');
require_once(__DIR__ . '/../../controller/utilisateurController.php');
session_start();

$recController = new RecompenseController();
$recompenses = $recController->listAll();
$utilisateurController = new UtilisateurController();

$id_utilisateur_connecte = 123;
$utilisateur = $utilisateurController->getUtilisateurById($id_utilisateur_connecte);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_rec'])) {
    $id_rec = $_POST['id_rec'];
    try {
        $recompense = $recController->read($id_rec);
        $cout = $recompense->getCout();
        
        if ($utilisateur->getPointsVerts() >= $cout) {
            $nouveaux_points = $utilisateur->getPointsVerts() - $cout;
            $utilisateurController->updatePointsVerts($id_utilisateur_connecte, $nouveaux_points);
            $utilisateur->setPointsVerts($nouveaux_points);

            $_SESSION['confirmation_data'] = [
                'code' => substr(md5(uniqid()), 0, 8),
                'nom_rec' => $recompense->getNomRec(),
                'cout' => $cout,
                'date' => date('d/m/Y H:i'),
                'partenaire' => $recompense->getNomPartenaire()
            ];
            $_SESSION['message'] = "Récompense acquise avec succès!";
        } else {
            $_SESSION['message'] = "Points insuffisants pour cette récompense.";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur: " . $e->getMessage();
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$message = $_SESSION['message'] ?? '';
$confirmation_data = $_SESSION['confirmation_data'] ?? null;
unset($_SESSION['message'], $_SESSION['confirmation_data']);

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
    <link rel="stylesheet" href="modal.css" />

    <link rel="stylesheet" href="promo-styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

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

        <!-- Modal  -->
        <?php if (!empty($confirmation_data)): ?>
        <div class="modal-overlay" id="confirmationModal">
            <div class="modal-content">
                <button class="modal-close" onclick="closeModal()">&times;</button>
                <div class="success-icon">✓</div>
                <h3>Confirmation de récompense</h3>
                <p>Vous avez obtenu : <strong><?= htmlspecialchars($confirmation_data['nom_rec']) ?></strong></p>
                <p>Partenaire : <strong><?= htmlspecialchars($confirmation_data['partenaire']) ?></strong></p>
                <p>Coût : <strong><?= htmlspecialchars($confirmation_data['cout']) ?> points verts</strong></p>
                
                <div class="confirmation-code">
                    <?= htmlspecialchars($confirmation_data['code']) ?>
                </div>
                
                <p>Présentez ce code au partenaire pour bénéficier de votre avantage</p>
                
                <div class="modal-actions">
                    <button onclick="printConfirmation()">
                        <i class="fas fa-print"></i> Imprimer
                    </button>
                    <button onclick="closeModal()">
                        <i class="fas fa-times"></i> Fermer
                    </button>
                </div>
            </div>
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
    <script>
        (function(){if(!window.chatbase||window.chatbase("getState")!=="initialized"){window.chatbase=(...arguments)=>{if(!window.chatbase.q){window.chatbase.q=[]}window.chatbase.q.push(arguments)};window.chatbase=new Proxy(window.chatbase,{get(target,prop){if(prop==="q"){return target.q}return(...args)=>target(prop,...args)}})}const onLoad=function(){const script=document.createElement("script");script.src="https://www.chatbase.co/embed.min.js";script.id="N9VVpJ57n-tJQCTWSzGgc";script.domain="www.chatbase.co";document.body.appendChild(script)};if(document.readyState==="complete"){onLoad()}else{window.addEventListener("load",onLoad)}})();
    
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('confirmationModal');
            if (modal) {
                setTimeout(() => {
                    modal.classList.add('active');
                }, 100);
            }
        });

        function closeModal() {
            const modal = document.getElementById('confirmationModal');
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        function printConfirmation() {
            const modalContent = document.querySelector('.modal-content').cloneNode(true);
            const printWindow = window.open('', '', 'width=600,height=600');
            
            // Retirer les boutons pour l'impression
            modalContent.querySelector('.modal-actions').remove();
            modalContent.querySelector('.modal-close').remove();
            modalContent.querySelector('.success-icon').remove();
            
            printWindow.document.write('<html><head><title>Confirmation Urbaverse</title>');
            printWindow.document.write('<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">');
            printWindow.document.write('<style>body { font-family: "Roboto", sans-serif; padding: 2rem; color: #333; }');
            printWindow.document.write('h3 { color: #2e7d32; margin-bottom: 1rem; }');
            printWindow.document.write('.confirmation-code { font-size: 1.5rem; padding: 1rem; background: #f5f5f5; border-radius: 8px; margin: 1rem 0; font-family: monospace; }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(modalContent.innerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
        }
    </script>
</body>
</html>