<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: /WaveNet/views/frontoffice/login.php');
    exit;
}

// Récupérer le titre du site depuis la configuration
require_once __DIR__ . '/../includes/config.php';
$pageTitle = "Sécurité du compte";
$activePage = 'dashboard';

// Récupérer le nom complet de l'utilisateur connecté
$nomUtilisateur = isset($_SESSION['user_prenom']) && isset($_SESSION['user_nom']) 
    ? $_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom'] 
    : 'votre compte';
?>

<?php include __DIR__ . '/../includes/userHeader.php'; ?>

<div class="container" style="padding-top: 7rem;">
    <div class="report">
        <div class="report-header">
            <h2>Sécurité du compte de <?php echo htmlspecialchars($nomUtilisateur); ?></h2>
            <p>L'authentification à deux facteurs (2FA) ajoute une couche de sécurité supplémentaire à votre compte.</p>
        </div>

        

        <div class="report-form">
            <h3>Authentification à deux facteurs (2FA)</h3>
           
            <?php if ($twofa_enabled): ?>
                <div class="alert-success">
                    <i class="fas fa-shield-alt"></i> L'authentification à deux facteurs est actuellement <strong>activée</strong> pour votre compte.
                </div>
                <a href="/WaveNet/controller/UserController.php?action=desactiver2FA" class="btn btn-secondary">
                    Désactiver l'authentification à deux facteurs
                </a>
            <?php else: ?>
                <div class="alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> L'authentification à deux facteurs est actuellement <strong>désactivée</strong> pour votre compte.
                </div>
                <div class="twofa-container">
                    <div class="twofa-steps">
                        <h3>Comment ça marche ?</h3>
                        <ol>
                            <li>Téléchargez une application d'authentification comme Google Authenticator, Authy ou Microsoft Authenticator sur votre téléphone.</li>
                            <li>Activez l'authentification à deux facteurs sur votre compte WaveNet.</li>
                            <li>Scannez le code QR avec votre application d'authentification ou saisissez manuellement la clé secrète.</li>
                            <li>Entrez le code à 6 chiffres généré par l'application pour vérifier la configuration.</li>
                            <li>La prochaine fois que vous vous connecterez, vous devrez entrer votre mot de passe puis le code généré par l'application.</li>
                        </ol>
                    </div>
                    <div class="twofa-action">
                        <a href="/WaveNet/controller/UserController.php?action=generer2FASecret" class="btn-2fa-activate">
                            Activer l'authentification à deux facteurs
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 2rem; text-align: center;">
            <a href="/WaveNet/views/frontoffice/userDashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>
    </div>
</div>

<style>
.twofa-container {
    display: flex;
    margin-top: 2rem;
    background-color: #f9f9f9;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.twofa-steps {
    flex: 3;
    padding-right: 20px;
}
.twofa-action {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    border-left: 1px solid #ddd;
    padding-left: 20px;
}
.btn-2fa-activate {
    display: inline-block;
    margin: 20px 0;
    padding: 12px 20px;
    background-color: #4CAF50;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-weight: bold;
    text-align: center;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s;
}
.btn-2fa-activate:hover {
    background-color: #45a049;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?> 