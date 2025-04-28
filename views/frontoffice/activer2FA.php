<?php
// Vérifier si l'utilisateur est connecté et si le secret temporaire existe
if (!isset($_SESSION['user_id']) || !isset($_SESSION['temp_2fa_secret'])) {
    header('Location: /WaveNet/views/frontoffice/userDashboard.php');
    exit;
}

// Récupérer le titre du site depuis la configuration
require_once __DIR__ . '/../includes/config.php';
$pageTitle = "Activation de l'authentification à deux facteurs";
$activePage = 'dashboard';
$secret = $_SESSION['temp_2fa_secret'];

// Récupérer le nom complet de l'utilisateur connecté
$nomUtilisateur = isset($_SESSION['user_prenom']) && isset($_SESSION['user_nom']) 
    ? $_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom'] 
    : 'votre compte';
?>

<?php include __DIR__ . '/../includes/userHeader.php'; ?>

<div class="container" style="padding-top: 7rem;">
    <div class="report">
        <div class="report-header">
            <h2>Activation de l'authentification à deux facteurs pour <?php echo htmlspecialchars($nomUtilisateur); ?></h2>
            <p>Suivez les étapes ci-dessous pour sécuriser votre compte</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-danger">
                <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            </div>
        <?php endif; ?>

        <div class="report-form">
            <h3>Étape 1: Configurer votre application d'authentification</h3>
            <p>Scannez le code QR ci-dessous avec votre application d'authentification (Google Authenticator, Authy, etc.) :</p>
            
            <div class="qr-container">
                <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code">
            </div>
            
            <div class="alert-info">
                <p><strong>Si vous ne pouvez pas scanner le code QR, saisissez cette clé manuellement :</strong></p>
                <div class="secret-key-container">
                    <input type="text" value="<?php echo $secret; ?>" readonly>
                    <button type="button" class="btn-copy" onclick="copySecret()">
                        Copier
                    </button>
                </div>
            </div>
        </div>

        <div class="report-form" style="margin-top: 2rem;">
            <h3>Étape 2: Vérifier la configuration</h3>
            <p>Entrez le code à 6 chiffres généré par votre application d'authentification :</p>
            
            <form action="/WaveNet/controller/UserController.php?action=activer2FA" method="post">
                <div class="form-group">
                    <label for="code_verification">Code de vérification</label>
                    <input type="text" id="code_verification" name="code_verification" 
                           placeholder="Entrez le code à 6 chiffres" required autocomplete="off" 
                           minlength="6" maxlength="6" pattern="[0-9]*">
                </div>
                
                <div class="form-actions">
                    <a href="/WaveNet/controller/UserController.php?action=gerer2FA" class="btn-cancel">
                        Annuler
                    </a>
                    <button type="submit" class="btn-verify">
                        Vérifier et activer
                    </button>
                </div>
            </form>
        </div>

        <div class="report-form" style="margin-top: 2rem;">
            <h3>Conseils de sécurité</h3>
            <ul>
                <li>Conservez une copie de votre clé secrète dans un endroit sûr.</li>
                <li>Si vous perdez l'accès à votre application d'authentification, vous aurez besoin de cette clé pour récupérer vos codes.</li>
                <li>Nous vous recommandons de sauvegarder vos codes de récupération fournis par votre application d'authentification.</li>
            </ul>
        </div>
    </div>
</div>

<style>
.btn-cancel {
    display: inline-block;
    padding: 10px 20px;
    background-color: #f8f9fa;
    color: #333;
    text-decoration: none;
    border-radius: 4px;
    border: 1px solid #ddd;
    margin-right: 15px;
}

.btn-verify {
    display: inline-block;
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
}

.btn-copy {
    padding: 8px 15px;
    background-color: #6c757d;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.form-actions {
    margin-top: 20px;
    display: flex;
    align-items: center;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
    
<script>
    function copySecret() {
        const secretInput = document.querySelector('input[readonly]');
        secretInput.select();
        document.execCommand('copy');
        alert('Clé secrète copiée !');
    }
</script> 