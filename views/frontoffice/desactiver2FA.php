<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: /WaveNet/views/frontoffice/login.php');
    exit;
}

// Récupérer le titre du site depuis la configuration
require_once __DIR__ . '/../includes/config.php';
$pageTitle = "Désactivation de l'authentification à deux facteurs";
$activePage = 'dashboard';
?>

<?php include __DIR__ . '/../includes/userHeader.php'; ?>

<div class="container" style="padding-top: 7rem;">
    <div class="report">
        <div class="report-header">
            <h2>Désactivation de l'authentification à deux facteurs</h2>
            <p>Pour désactiver l'authentification à deux facteurs, veuillez confirmer votre identité</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-danger">
                <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            </div>
        <?php endif; ?>

        <div class="report-form">
            <div class="alert-warning">
                <p><i class="fas fa-exclamation-triangle"></i> <strong>Attention :</strong> Désactiver l'authentification à deux facteurs réduira la sécurité de votre compte.</p>
            </div>
            
            <form action="/WaveNet/controller/UserController.php?action=desactiver2FA" method="post">
                <div class="form-group">
                    <label for="password">Mot de passe actuel</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Entrez votre mot de passe actuel" required>
                </div>
                
                <div class="form-group">
                    <label for="code_verification">Code de vérification</label>
                    <input type="text" id="code_verification" name="code_verification" 
                           placeholder="Entrez le code à 6 chiffres" required autocomplete="off" 
                           minlength="6" maxlength="6" pattern="[0-9]*">
                    <small class="hint-text">
                        Généré par votre application d'authentification
                    </small>
                </div>
                
                <div class="form-actions">
                    <a href="/WaveNet/controller/UserController.php?action=gerer2FA" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-circle"></i> Désactiver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?> 