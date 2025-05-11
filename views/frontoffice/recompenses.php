<?php
require_once(__DIR__ . '/../../controller/partenaireController.php');
require_once(__DIR__ . '/../../controller/recompenseController.php');
require_once(__DIR__ . '/../../controller/userController.php');
require_once(__DIR__ . '/../../views/includes/config.php');

// Vérifier si une session est déjà active avant d'en démarrer une nouvelle
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définir la page active pour la navigation
$activePage = 'recompenses';
$pageTitle = 'Récompenses - WaveNet';

$recController = new RecompenseController();
$recompenses = $recController->listAll();
$userController = new UserController();

// ID de l'utilisateur connecté (récupéré depuis la session)
$id_utilisateur_connecte = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$utilisateur = null;

// Récupérer les informations utilisateur depuis la base de données
if ($id_utilisateur_connecte > 0) {
    require_once(__DIR__ . '/../../models/Utilisateur.php');
    require_once(__DIR__ . '/../../views/includes/config.php');
    $db = connectDB();
    $utilisateur = Utilisateur::findById($db, $id_utilisateur_connecte);
}

// Traitement de la confirmation de récompense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_rec'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['message'] = "Veuillez vous connecter pour obtenir cette récompense.";
        header("Location: /WaveNet/views/frontoffice/login.php");
        exit();
    }
    
    $id_rec = $_POST['id_rec'];
    try {
        $recompense = $recController->read($id_rec);
        $cout = $recompense->getCout();
        
        if ($utilisateur && $utilisateur->getPointsVerts() >= $cout) {
            // Calculer les nouveaux points après l'achat de la récompense
            $nouveaux_points = $utilisateur->getPointsVerts() - $cout;
            
            // Mettre à jour les points de l'utilisateur dans la base de données
            $utilisateur->setPointsVerts($nouveaux_points);
            $utilisateur->update($db);
            
            // Enregistrer dans la session pour l'affichage après redirection
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

    // Redirection pour éviter le repost
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Récupérer les messages depuis la session après redirection
$message = $_SESSION['message'] ?? '';
$confirmation_data = $_SESSION['confirmation_data'] ?? null;
unset($_SESSION['message'], $_SESSION['confirmation_data']);

function isPromo($dateFin) {
    $now = new DateTime();
    $endDate = new DateTime($dateFin);
    $interval = $now->diff($endDate);
    return $interval->days <= 7 && $interval->invert == 0;
}

// Inclure les styles de la page récompenses
echo '<link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css">';

// Inclure l'en-tête
include_once(__DIR__ . '/../includes/header.php');
?>

<!-- Garder uniquement le contenu principal, sans les balises HTML/head/body qui sont déjà dans header.php -->
<main class="recompenses">
    <?php if (!empty($message)): ?>
    <div class="alert <?= strpos($message, 'Erreur') !== false || strpos($message, 'insuffisants') !== false ? 'alert-error' : 'alert-success' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <!-- Modal de confirmation -->
    <?php if (!empty($confirmation_data)): ?>
    <div class="modal-overlay active" id="confirmationModal">
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
            <?php if (empty($recompenses)): ?>
                <p class="no-recompenses">Aucune récompense disponible pour le moment.</p>
            <?php else: ?>
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
                                <?= (!$utilisateur || $utilisateur->getPointsVerts() < $rec->getCout()) ? 'disabled' : '' ?>>
                                Confirmer
                            </button>
                            <?php if (!$utilisateur): ?>
                            <p class="points-insuffisants">Connectez-vous pour obtenir cette récompense</p>
                            <?php elseif ($utilisateur->getPointsVerts() < $rec->getCout()): ?>
                            <p class="points-insuffisants">Vous n'avez pas assez de points pour cette récompense</p>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    // Afficher le modal avec animation
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('confirmationModal');
        if (modal) {
            setTimeout(() => {
                modal.classList.add('active');
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.style.opacity = "1";
                }
            }, 100);
        }
        
        // Mettre à jour les compteurs de temps restant
        const countdowns = document.querySelectorAll('.countdown');
        countdowns.forEach(countdown => {
            const endDate = new Date(countdown.dataset.endDate);
            updateCountdown(countdown, endDate);
            setInterval(() => updateCountdown(countdown, endDate), 1000);
        });
    });

    function updateCountdown(element, endDate) {
        const now = new Date();
        const diff = endDate - now;
        
        if (diff <= 0) {
            element.textContent = "Offre expirée!";
            return;
        }
        
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        element.textContent = `Expire dans: ${days}j ${hours}h ${minutes}m ${seconds}s`;
    }

    function closeModal() {
        const modal = document.getElementById('confirmationModal');
        const modalContent = modal.querySelector('.modal-content');
        
        if (modalContent) {
            modalContent.style.opacity = "0";
        }
        
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
        
        printWindow.document.write('<html><head><title>Confirmation WaveNet</title>');
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

<?php
// Inclure le pied de page
include_once(__DIR__ . '/../includes/footer.php');
?>