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
                        <div class="password-strength-meter mt-2">
                            <div class="password-strength-bar"></div>
                            <div class="password-strength-text">Force du mot de passe</div>
                        </div>
                        <div class="error-message" id="password-error"></div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div class="error-message" id="confirm-error"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Réinitialiser le mot de passe</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.password-strength-meter {
    width: 100%;
}
.password-strength-bar {
    height: 8px;
    border-radius: 4px;
    background-color: #ccc; /* gris par défaut */
    width: 0%;
    transition: width 0.3s, background-color 0.3s;
}
.password-strength-text {
    font-size: 0.85rem;
    margin-top: 5px;
    color: #666;
}
.strength-weak .password-strength-bar {
    background-color: #ff4d4d; /* rouge */
    width: 33%;
}
.strength-medium .password-strength-bar {
    background-color: #ffa64d; /* orange */
    width: 66%;
}
.strength-strong .password-strength-bar {
    background-color: #4CAF50; /* vert */
    width: 100%;
}
.error-message {
    color: #dc3545;
    font-size: 0.85rem;
    margin-top: 5px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('new_password');
    const confirmInput = document.getElementById('confirm_password');
    const passwordError = document.getElementById('password-error');
    const confirmError = document.getElementById('confirm-error');
    const form = document.querySelector('form');
    
    // Fonction pour vérifier la force du mot de passe
    function checkPasswordStrength(password) {
        const strengthMeter = document.querySelector('.password-strength-meter');
        const strengthText = document.querySelector('.password-strength-text');
        
        // Réinitialiser les classes
        strengthMeter.classList.remove('strength-weak', 'strength-medium', 'strength-strong');
        
        // Si le champ est vide
        if (password.length === 0) {
            strengthText.textContent = 'Force du mot de passe';
            return 0;
        }
        
        // Critères de force
        const hasMinLength = password.length >= 8;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSpecialChar = /[^A-Za-z0-9]/.test(password);
        
        // Calculer le score
        let score = 0;
        if (hasMinLength) score++;
        if (hasUpperCase) score++;
        if (hasNumber) score++;
        if (hasSpecialChar) score++;
        
        // Mettre à jour l'interface en fonction du score
        if (score === 0) {
            strengthText.textContent = 'Force du mot de passe';
        } else if (score <= 2) {
            strengthMeter.classList.add('strength-weak');
            strengthText.textContent = 'Faible';
        } else if (score === 3) {
            strengthMeter.classList.add('strength-medium');
            strengthText.textContent = 'Moyen';
        } else {
            strengthMeter.classList.add('strength-strong');
            strengthText.textContent = 'Fort';
        }
        
        return score;
    }
    
    // Validation en temps réel du mot de passe
    passwordInput.addEventListener('input', function() {
        const score = checkPasswordStrength(this.value);
        
        // Vérification en temps réel
        const hasMinLength = this.value.length >= 8;
        const hasUpperCase = /[A-Z]/.test(this.value);
        const hasNumber = /[0-9]/.test(this.value);
        const hasSpecialChar = /[^A-Za-z0-9]/.test(this.value);
        
        if (hasMinLength && hasUpperCase && hasNumber && hasSpecialChar) {
            passwordError.textContent = '';
        } else {
            const missingCriteria = [];
            if (!hasMinLength) missingCriteria.push('8 caractères minimum');
            if (!hasUpperCase) missingCriteria.push('1 majuscule');
            if (!hasNumber) missingCriteria.push('1 chiffre');
            if (!hasSpecialChar) missingCriteria.push('1 caractère spécial');
            
            passwordError.textContent = 'Le mot de passe doit contenir: ' + missingCriteria.join(', ');
        }
        
        // Vérifier également la confirmation
        if (confirmInput.value && this.value !== confirmInput.value) {
            confirmError.textContent = 'Les mots de passe ne correspondent pas';
        } else if (confirmInput.value) {
            confirmError.textContent = '';
        }
    });
    
    // Vérification en temps réel de la confirmation
    confirmInput.addEventListener('input', function() {
        if (this.value === passwordInput.value) {
            confirmError.textContent = '';
        } else {
            confirmError.textContent = 'Les mots de passe ne correspondent pas';
        }
    });
    
    // Validation à la soumission du formulaire
    form.addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        let isValid = true;
        
        // Vérifier la robustesse du mot de passe
        const score = checkPasswordStrength(password);
        if (score < 4) {
            e.preventDefault();
            isValid = false;
            
            const hasMinLength = password.length >= 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecialChar = /[^A-Za-z0-9]/.test(password);
            
            const missingCriteria = [];
            if (!hasMinLength) missingCriteria.push('8 caractères minimum');
            if (!hasUpperCase) missingCriteria.push('1 majuscule');
            if (!hasNumber) missingCriteria.push('1 chiffre');
            if (!hasSpecialChar) missingCriteria.push('1 caractère spécial');
            
            passwordError.textContent = 'Le mot de passe doit contenir: ' + missingCriteria.join(', ');
        }
        
        // Vérifier que les mots de passe correspondent
        if (password !== confirm) {
            e.preventDefault();
            isValid = false;
            confirmError.textContent = 'Les mots de passe ne correspondent pas';
        }
        
        return isValid;
    });
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php'; 
// Nettoyer le token de la session une fois la page affichée pour éviter réutilisation?
// unset($_SESSION['reset_token']); // Attention: si l'utilisateur recharge la page après erreur, ça casse.
// Il vaut mieux le garder jusqu'à la soumission réussie du formulaire.
?> 