<?php
session_start();
// Initialiser les variables requises par header.php
$pageTitle = "Connexion";
$activePage = "login";

// Réinitialisation du compteur de tentatives de captcha lors de l'accès à la page de login
if (!isset($_GET['keep_attempts'])) {
    $_SESSION['captcha_attempts'] = 0;
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Ajouter le lien vers style11.css -->
<link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css">
<!-- Inclure le fichier captcha.css -->
<link rel="stylesheet" href="/WaveNet/views/assets/css/captcha.css">

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="report-form">
                <h2>Connexion</h2>
                <p>Veuillez vous connecter pour accéder à votre compte WaveNet</p>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div style="background-color: rgba(244, 67, 54, 0.1); border-left: 3px solid #f44336; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                        <p style="margin: 0; color: #d32f2f;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert-success">
                        <p><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
                    </div>
                <?php endif; ?>
                
                <!-- Formulaire de connexion normal (toujours affiché) -->
                <form id="login-form" action="/WaveNet/controller/UserController.php?action=checkCredentials" method="post">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                        <div class="error-message" id="email-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <div style="position: relative;">
                            <input type="password" class="form-control" id="password" name="password">
                            <button type="button" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-message" id="password-error"></div>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Se souvenir de moi</label>
                    </div>
                    
                    <!-- Remplacement de la checkbox par le bouton déclencheur -->
                    <div class="form-group captcha-trigger-container" style="margin-top: 15px;">
                        <button type="button" id="captcha-trigger-button" class="btn btn-secondary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 10px;">
                            <i class="fas fa-shield-alt"></i>
                            <span>Vérification de sécurité</span>
                        </button>
                        <div id="captcha-status" style="margin-top: 10px; text-align: center; height: 20px;">
                            <!-- Statut du captcha (ex: checkmark) ira ici -->
                        </div>
                    </div>
                    
                    <button type="submit" id="login-submit-button" class="btn btn-primary" style="margin-top: 20px;" disabled>Se connecter</button>
                    <div id="login-spinner" class="spinner" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Vérification en cours...
                    </div>
                    <!-- Div for displaying AJAX login feedback -->
                    <div id="login-feedback" style="margin-top: 15px; color: red; text-align: center; min-height: 20px;"></div>
                </form>
                
                <div class="mt-3 text-center">
                    <p>Pas encore de compte? <a href="register.php">Inscrivez-vous</a></p>
                    <p><a href="forgot_password_request.php">Mot de passe oublié?</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour l'eco-captcha -->
<div id="captcha-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3>Vérification de sécurité</h3>
        <p>Pour finaliser votre connexion, veuillez compléter le CAPTCHA ci-dessous</p>
        
        <div id="eco-captcha-container">
            <!-- Le contenu du eco-captcha sera chargé ici dynamiquement -->
        </div>
    </div>
</div>

<style>
/* Styles pour la fenêtre modale */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.6);
    text-align: center;
}

.modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    width: auto;
    max-width: 420px;
    position: relative;
    display: inline-block;
    text-align: left;
}

.close-modal {
    position: absolute;
    top: 8px;
    right: 12px;
    font-size: 22px;
    font-weight: bold;
    cursor: pointer;
    color: #777;
}

.close-modal:hover {
    color: #333;
}

/* Assurez-vous que le conteneur du captcha ne dépasse pas */
#eco-captcha-container {
    max-width: 100%;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    
    if (togglePassword) {
        togglePassword.addEventListener('click', function(e) {
            e.preventDefault();
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.querySelectorAll('.fa').forEach(icon => {
                icon.classList.toggle('d-none');
            });
        });
    }
    
    // Formulaire de validation
    const loginForm = document.getElementById('login-form');
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('email-error');
    const passwordInput = document.getElementById('password');
    const passwordError = document.getElementById('password-error');
    
    // Validation lors de la soumission du formulaire
    loginForm.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validation de l'email
        if (!emailInput.value.trim()) {
            emailError.textContent = 'Veuillez saisir votre adresse email';
            emailError.style.color = 'red';
            isValid = false;
        } else if (!isValidEmail(emailInput.value.trim())) {
            emailError.textContent = 'Veuillez saisir une adresse email valide';
            emailError.style.color = 'red';
            isValid = false;
        } else {
            emailError.textContent = '';
        }
        
        // Validation du mot de passe
        if (!passwordInput.value.trim()) {
            passwordError.textContent = 'Veuillez saisir votre mot de passe';
            passwordError.style.color = 'red';
            isValid = false;
        } else {
            passwordError.textContent = '';
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Validation en temps réel
    emailInput.addEventListener('input', function() {
        if (this.value.trim() && isValidEmail(this.value.trim())) {
            emailError.textContent = '';
        }
    });
    
    passwordInput.addEventListener('input', function() {
        if (this.value.trim()) {
            passwordError.textContent = '';
        }
    });
    
    // Fonction pour valider l'email
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});
</script>

<!-- Inclusion du nouveau fichier JS (defer pour exécution après parsing HTML) -->
<script src="/WaveNet/views/assets/js/eco-captcha-handler.js" defer></script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
