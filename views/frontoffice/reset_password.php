<?php
// La session est déjà démarrée par showResetPasswordForm()
// session_start(); 

$pageTitle = "Réinitialiser le mot de passe";
$activePage = "login";

require_once __DIR__ . '/../includes/header.php'; 

// Récupérer le token depuis la session (placé par showResetPasswordForm)
// Ou vous pourriez le passer en variable depuis le contrôleur
$token = $_SESSION['reset_token'] ?? null;

// Sécurité: si pas de token en session, rediriger
if (!$token) {
    $_SESSION['error'] = "Session de réinitialisation invalide ou expirée.";
    header('Location: /WaveNet/views/frontoffice/login.php');
    exit;
}

?>

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="report-form">
                <h2>Choisissez un nouveau mot de passe</h2>
                <p>Veuillez entrer votre nouveau mot de passe ci-dessous.</p>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): // Peut être utilisé après succès
                    // Normalement on redirige, mais on peut afficher un message ici si besoin
                    ?>
                    <div class="alert alert-success">
                        <p><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
                    </div>
                <?php endif; ?>

                <form action="/WaveNet/controller/UserController.php" method="post">
                    <!-- Champ caché pour l'action -->
                    <input type="hidden" name="action" value="handleResetPassword">
                    <!-- Champ caché pour envoyer le token -->
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="form-group mb-3">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Réinitialiser le mot de passe</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php'; 
// Nettoyer le token de la session une fois la page affichée pour éviter réutilisation?
// unset($_SESSION['reset_token']); // Attention: si l'utilisateur recharge la page après erreur, ça casse.
// Il vaut mieux le garder jusqu'à la soumission réussie du formulaire.
?> 